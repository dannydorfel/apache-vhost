<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-08 14:19
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Console\Command;

use Netvlies\ApacheVhost\Config\BaseConfig;
use Netvlies\ApacheVhost\System\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class InitCommand
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\Console\Command
 */
class InitCommand extends ApacheVhostCommand
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
            ->setName('init')
            ->setDefinition(array(
                new InputOption('config-dir', 'c', InputOption::VALUE_REQUIRED, 'The configuration path for output', null),
                new InputOption('vhosts-dir', 'd', InputOption::VALUE_REQUIRED, 'The vhosts directory to use as default', null),
                new InputOption('hostname', '', InputOption::VALUE_REQUIRED, 'The hostname to record as default', null),
                new InputOption('force-update', '', InputOption::VALUE_NONE, 'If added, an existing configuration will be updated')
            ))
            ->setDescription('Creates the parameters config')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command will create the vhost parameters file.

    <info>php %command.full_name%</info>

The <comment>--file</comment> changes the file to write the config to, this script creates the .apache_vhosts.php in the user's homedirectory by default:

    <info>php %command.full_name% --file=/path/to/dir/my-config.conf</info>
EOF
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        /** @var $environment Environment */
        $environment = $this->getApplication()->getEnvironment();

        $home = $environment->getHome($environment->getCurrentUser());

        $hostname = $input->getOption('hostname') ? $input->getOption('hostname') : $environment->getSystemHostName();
        $configDir = $input->getOption('config-dir') ? $input->getOption('config-dir') : realpath($home) . '/.httpd';
        $vhostsDir = $input->getOption('vhosts-dir') ? $input->getOption('vhosts-dir') : realpath($home) . '/vhosts';

        if (file_exists($configDir) && ! $input->getOption('force-update')) {
            $output->writeln('<error>Config directory already exists, use --force-update for updating the config</error>');
            return 1;
        }

        $config = new BaseConfig();
        $config->setConfigDir($configDir)
            ->setVhostsDir($vhostsDir);

        $result = $this->ensureCreated($config, $this->getHelperSet()->get('dialog'), $output);

        if (! $result) {
            $this->output->writeln('An error occured creating/accessing the directories');
            return 1;
        }

        $config->setHostname($hostname);

        $file = $config->getConfigDir() . '/directory_config.yml';
        file_put_contents($file, Yaml::dump($config->toArray()));
        return 0;
    }

    /**
     * @param BaseConfig $directoryConfig
     * @param DialogHelper $dialog
     * @param OutputInterface $output
     * @return bool
     */
    protected function ensureCreated(BaseConfig $directoryConfig, DialogHelper $dialog, OutputInterface $output)
    {
        $dialogQuestion = "<question>The directory %s does not exist yet. Create it?</question> (Y/N) ";

        $confirm = function ($dir) use ($dialog, $output, $dialogQuestion) {
            return $dialog->askConfirmation($output, sprintf($dialogQuestion, $dir), false);
        };

        foreach (array('getConfigDir', 'getVhostsDir') as $callable) {
            if (! $this->ensureDirectory($directoryConfig->$callable(), $confirm)) {
                return false;
            }
        }

        foreach (array('getSslSitesDir', 'getSslKeyDir', 'getSitesDir') as $callable) {
            if (! $this->ensureDirectory($directoryConfig->$callable())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $dir
     * @param callable $dialogCallback
     * @return bool
     */
    protected function ensureDirectory($dir, \Closure $dialogCallback = null)
    {
        $result = true;
        if (! file_exists($dir)) {
            if (is_null($dialogCallback) || $dialogCallback($dir)) {
                $result = mkdir($dir, 0777, true);
            } else {
                $result = false;
            }
        }
        return $result;
    }
}
