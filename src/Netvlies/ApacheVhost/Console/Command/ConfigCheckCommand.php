<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-06-21 14:47
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Console\Command;

use Netvlies\ApacheVhost\Config\DirectoryConfig;
use Netvlies\ApacheVhost\Config\HttpdConfig;
use Netvlies\ApacheVhost\System\Environment;
use Symfony\Component\Console\Command\Command;
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
class ConfigCheckCommand extends ApacheVhostCommand
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
            ->setName('config:check')
            ->setDefinition(array(
                new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'The config file to use', null),
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $this->determineConfigFile($input->getOption('config'));

        if (! file_exists($configFile)) {
            $output->writeln("<error>The config file cannot be found: $configFile</error>");
            return 1;
        }

        try {
            $config = DirectoryConfig::fromYmlFile($configFile);
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return 1;
        }

        $output->writeln("<info>Directory config found and ok</info>");
        return 0;
    }
}
