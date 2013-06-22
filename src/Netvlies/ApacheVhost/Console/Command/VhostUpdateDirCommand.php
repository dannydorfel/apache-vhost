<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-07 15:58
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Console\Command;

use Netvlies\ApacheVhost\Config\BaseConfig;
use Netvlies\ApacheVhost\Vhost\SubdomainVhost;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 */
class VhostUpdateDirCommand extends ApacheVhostCommand
{
    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('vhost:update-dir')
            ->setDefinition(array(
                new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'The config file to use', null),
                new InputOption('dir', 'd', InputOption::VALUE_REQUIRED, 'The directory to crawl and update, defaults to the dir from config', null),
                new InputOption('hostname', '', InputOption::VALUE_REQUIRED, 'Which hostname to use (mytestdomain.nl => project.mytestdomain.nl), defaults to system hostname'),
                new InputOption('dry-run', '', InputOption::VALUE_NONE, 'Only shows which files would have been modified'),
            ))
            ->setDescription('Creates/updates vhosts by crawling a directory')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command will crawl the create the (given) directory and updates the vhosts declaration:

    <info>php %command.full_name%</info>

The <comment>--host</comment> changes the main domain, this script uses the system's hostname by default:

    <info>php %command.full_name% /path/to/dir --host=mytestdomain.nl</info>
EOF
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $this->determineConfigFile($input->getOption('config'));
        if (! $this->checkConfig($configFile)) {
            $output->writeln('<error>There is a problem with the config, run check:config for more information');
            return 1;
        }

        $config = BaseConfig::fromYmlFile($configFile);
        $hostname = $input->getOption('hostname') ? $input->getOption('hostname') : $config->getHostname();
        $vhostDirectory = $input->getOption('dir') ? $input->getOption('dir') : $config->getVhostsDir();

        $finder = new Finder();
        foreach ($finder->directories()->depth('== 0')->in($vhostDirectory) as $directory) {
            $this->callCreateVhost($directory, $hostname, $output);
        }

//        if ($this->input->hasOption('cleanup')) {
    //        $this->cleanupVhosts();
    //        $this->cleanupSslVhosts();

        return empty($changed) ? 0 : 1;
    }

    protected function callCreateVhost($directory, $hostname, $output)
    {
        // check the config
        $command = $this->getApplication()->find('vhost:create');
        $input = new ArrayInput(array('command' => 'vhost:create', '--path' => $directory, '--hostname' => $hostname));
        return $command->run($input, $output) == 0;
    }

    public function createSsl($vhost)
    {

    }

    protected function createSslVhost($vhost)
    {
        $outputFile = $configPath . $config['hostname'] . '.conf';

        if ($this->input->getOption('verbose')) {
            if (file_exists($outputFile)) {
                $this->output->writeln("<info>Updating " . $outputFile . "...</info>") ;
            } else {
                $this->output->writeln("<info>Creating " . $outputFile . "...</info>") ;
            }
        }

        $vhost = $config['hostname'];
        $certFile = "$certificatePath/$vhost.crt";
        if(!file_exists($certFile)) {
            $privateKeyFile = "$privateKeyPath/$vhost.key";
            // Default create SSL certificate as well
            exec("openssl genrsa -out $privateKeyFile 1024");
            exec("openssl req -new -key $privateKeyFile -x509 -out $certFile -days 999 -subj '/C=NL/ST=NB/L=Breda/CN=$vhost'");
        }

        extract($config);
//        ob_start();
        var_dump(include __DIR__ . '/../Resources/views/vhostSsl.php'); die;
//        $vhostDeclaration = ob_get_contents();
//        ob_end_clean();

        //todo: test if we can write here (are you permitted? Run as sudo)
        file_put_contents($outputFile, $vhostDeclaration);
    }

    protected function cleanupVhosts($configPath = '/etc/httpd/sites.d/')
    {
        $sites = glob($configPath . '*.conf');

        foreach ($sites as $site) {
            $config = file_get_contents($site);

            if (preg_match('/DocumentRoot (.*)/', $config, $match) && ! file_exists($match[1])) {
                $this->output->writeln("<info>Removing $site</info>");
                unlink($site);
            }
        }
    }

    protected function cleanupSslVhosts($configPath = '/etc/httpd/sslsites.d/')
    {
        $sites = glob($configPath . '*.conf');

        foreach ($sites as $site) {
            $config = file_get_contents($site);

            if (preg_match('/DocumentRoot (.*)/', $config, $match) && ! file_exists($match[1])) {
                $this->output->writeln("<info>Removing $site</info>");
                unlink($site);
                if (preg_match('/ServerName (.*)/', $config, $match)) {
                    $certificatePath = '/etc/pki/tls/certs';
                    $privateKeyPath = '/etc/pki/tls/private';

                    $cert = $certificatePath . '/' . $match[1] . '.crt';
                    if (file_exists($cert)) {
                        $this->output->writeln("<info>Removing certificate $cert</info>");
                        unlink($cert);
                    }

                    $key = $privateKeyPath . '/' . $match[1] . '.key';
                    if (file_exists($key)) {
                        $this->output->writeln("<info>Removing private key $key</info>");
                        unlink($key);
                    }
                }
            }
        }
    }
}
