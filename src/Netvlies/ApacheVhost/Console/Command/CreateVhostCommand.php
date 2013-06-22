<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-06-21 14:38
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Console\Command;

use Netvlies\ApacheVhost\Config\BaseConfig;
use Netvlies\ApacheVhost\Vhost\SubdomainVhost;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 */
class CreateVhostCommand extends ApacheVhostCommand
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
            ->setName('vhost:create')
            ->setDefinition(array(
                new InputOption('path', '', InputOption::VALUE_REQUIRED, 'The path to the web directory'),
                new InputOption('hostname', '', InputOption::VALUE_REQUIRED, 'The hostname for the vhost'),
                new InputOption('config', '', InputOption::VALUE_REQUIRED, 'The config file to use'),
                new InputOption('sites-dir', '', InputOption::VALUE_REQUIRED, 'The sites directory to write the files'),
//                new InputOption('dry-run', '', InputOption::VALUE_NONE, 'Only shows which files would have been modified'),
            ))
            ->setDescription('Creates vhosts')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command will create a new vhost by the given directory and vhost options:

    <info>php %command.full_name% /path/to/dir myvhost.mydomain.tld</info>
EOF
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->verbose = $input->getOption('verbose');

        $configFile = $this->determineConfigFile($input->getOption('config'));

        if (! $this->checkConfig($configFile)) {
            $output->writeln('<error>There is a problem with the config, run check:config for more information');
            return 1;
        }

        $config = BaseConfig::fromYmlFile($configFile);
        $path = $input->getOption('path') ? $input->getOption('path') : $config->getVhostsDir();
        $hostname = $input->getOption('hostname') ? $input->getOption('hostname') : $config->getHostname();
        $confDir = $input->getOption('sites-dir') ? $input->getOption('sites-dir') : $config->getSitesDir();

        $vhost = $this->createVhost($path, $hostname, $confDir);

        // TODO: save vhost object to use later...

        return empty($changed) ? 0 : 1;
    }

    protected function createVhost(\SplFileInfo $directory, $hostname, $sitesDir)
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

        $file = $sitesDir . '/' . $vhost->getServerName() . '.conf';
        $actionString = file_exists($file) ? 'Updated' : 'Created';
        file_put_contents($file, $declaration);

        if ($this->verbose) {
            $this->output->writeln("<info>$actionString " . $file . "</info>") ;
        }

        return $vhost;
    }
}
