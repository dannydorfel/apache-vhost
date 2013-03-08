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
 * Class CreateConfigCommand
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\Console\Command
 */
class InitCommand extends Command
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
                new InputOption('config-dir', 'c', InputOption::VALUE_OPTIONAL, 'The configuration path for output', null),
                new InputOption('vhosts-dir', 'd', InputOption::VALUE_OPTIONAL, 'The vhosts directory to use', null),
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
        $this->input = $input;
        $this->output = $output;

        /** @var $environment Environment */
        $environment = $this->getApplication()->getEnvironment();

        $home = $environment->getHome($environment->getCurrentUser());

        $configDir = $input->hasOption('config-dir') && $input->getOption('config-dir')
            ? $input->getOption('config-dir') : realpath($home) . '/.httpd';

        $vhostsDir = $input->hasOption('vhosts-dir') && $input->getOption('vhosts-dir')
            ? $input->getOption('vhosts-dir') : realpath($home) . '/vhosts';

        $directoryConfig = new DirectoryConfig();
        $directoryConfig->setConfigDir($configDir)
            ->setVhostsDir($vhostsDir);

        $result = $directoryConfig->ensureCreated($this->getHelperSet()->get('dialog'), $this->output);

        if (! $result) {
            $this->output->writeln('An error occured creating/accessing the directories');
            return 1;
        }

        $file = $directoryConfig->getConfigDir() . '/directory_config.yml';
        file_put_contents($file, Yaml::dump($directoryConfig->toArray()));
        return 0;
    }

    protected function ensureCreated($dir)
    {
        $dialogQuestion = "<question>The directory $dir does not exist yet. Create it?</question> ";
        $dialog = $this->getHelperSet()->get('dialog');

        $result = true;
        if (! file_exists($dir)) {
            if (!$dialog->askConfirmation($this->output, $dialogQuestion, false)) {
                return false;
            }
            $result = mkdir($dir, 0777, true);
        }
        return $result;
    }
}
