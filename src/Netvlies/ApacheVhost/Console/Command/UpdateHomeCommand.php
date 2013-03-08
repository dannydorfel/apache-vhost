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

use Netvlies\ApacheVhost\Config\HomeConfig;
use Netvlies\ApacheVhost\System\Environment;
use Netvlies\ApacheVhost\Vhost\SubdomainVhost;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 */
class UpdateHomeCommand extends Command
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
            ->setName('update-home')
            ->setDefinition(array(
//                new InputArgument('path', InputArgument::REQUIRED, 'The path'),
//                new InputOption('config', '', InputOption::VALUE_OPTIONAL, 'The configuration name', null),
//                new InputOption('host', '', InputOption::VALUE_OPTIONAL, 'Which hostname to use (mytestdomain.nl => project.mytestdomain.nl)'),
//                new InputOption('dry-run', '', InputOption::VALUE_NONE, 'Only shows which files would have been modified'),
            ))
            ->setDescription('Creates vhosts')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command will create a new vhost by the path:

    <info>php %command.full_name% /path/to/dir</info>

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
        $this->input = $input;
        $this->output = $output;

        /** @var $environment Environment */
        $environment = $this->getApplication()->getEnvironment();

        // todo: fetch config file or build from defaults
        $home = $environment->getHome($environment->getCurrentUser());
        $config = new HomeConfig($home);
        $config->ensureDirectories();

        $hostname = $environment->getSystemHostName();

        $finder = new Finder();
        foreach ($finder->directories()->depth('== 0')->in($config->getHome() . '/vhosts') as $directory) {
            $vhost = $this->createVhost($directory, $hostname, $config);

            if (($vhost !== false) && $config->useSsl()) {
                $this->createSsl($vhost);
                $this->createSslVhost($vhost);
            }
        }

        if ($this->input->hasOption('cleanup')) {
    //        $this->cleanupVhosts();
    //        $this->cleanupSslVhosts();
        }

        return empty($changed) ? 0 : 1;
    }

//    protected function handleVhost($vhost, $documentRoot)
//    {
//        $config = array(
//            'hostname' => $vhost,
//            'adminEmail' => 'ddorfel@netvlies.nl',
//            'documentRoot' => $documentRoot,
//
//            'errorLog' => null,
//            'transferLog' => null,
//            'xdebugProfiler' => null,
//            'xdebugProfilerOutputDir' => null,
//            'xDebugTraceOutputDir' => null,
//        );
//
//        $this->createVhost($config);
//        $this->createSslVhost($config);
//    }

    protected function createVhost(\SplFileInfo $directory, $hostname, $config)
    {
        $serverName = $directory->getFilename() . '.' . $hostname;
        $options = array();
        $vhost = new SubdomainVhost($serverName, $directory, $options);

        if (! $vhost->isValid()) {
            if ($this->input->getOption('verbose')) {
                $this->output->writeln("<error>Skipping invalid hostname '" . $vhost . "'</error>");
            }
            return false;
        }

        $declaration = $vhost->process(array());

        $file = $config->getSitesDir() . '/' . $vhost->getServerName() . '.conf';
        $actionString = file_exists($file) ? 'Updated' : 'Created';
        file_put_contents($file, $declaration);

        if ($this->input->getOption('verbose')) {
            $this->output->writeln("<info>$actionString " . $file . "</info>") ;
        }

        return $vhost;
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
        ob_start();
        include __DIR__ . '/../Resources/views/vhostSsl.php';
        $vhostDeclaration = ob_get_contents();
        ob_end_clean();

        //todo: test if we can write here (are you permitted? Run as sudo)
        file_put_contents($outputFile, $vhostDeclaration);
    }

    protected function findBasePath($root = null)
    {
        $basePath = is_null($root) ? getcwd() : $root;

        if(file_exists($basePath . '/current/web')){
            // For capistrano deployments
            $basePath .= '/current/web';
        } elseif(file_exists($basePath . '/current')) {
            // For capifony deployments
            $basePath .= '/current';
        } elseif(file_exists($basePath . '/web')) {
            // For default symfony projects
            $basePath .= '/web';
        }

        return $basePath;
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
