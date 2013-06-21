<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * Author Danny Dörfel <ddorfel@netvlies.nl>
 * Created: 6/21/13 3:41 PM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Netvlies\ApacheVhost\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;

abstract class ApacheVhostCommand extends Command
{
    protected function checkConfig($file)
    {
        $output = new NullOutput();

        // check the config
        $command = $this->getApplication()->find('config:check');
        $input = new ArrayInput(array('--config' . $file));
        return $command->run($input, $output) == 0;
    }

    protected function determineConfigFile($file)
    {
        /** @var $environment \Netvlies\ApacheVhost\System\Environment */
        $environment = $this->getApplication()->getEnvironment();
        $home = $environment->getHome($environment->getCurrentUser());

        return $file ? $file : realpath($home) . '/.httpd/directory_config.yml';
    }
}
