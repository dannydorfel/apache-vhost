<?php
/**
 * (c) Netvlies Internetdiensten
 *
 * author D. Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-07 14:09
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Console;

use Netvlies\ApacheVhost\Console\Command\ConfigCheckCommand;
use Netvlies\ApacheVhost\Console\Command\CreateVhostCommand;
use Netvlies\ApacheVhost\Console\Command\InitCommand;
use Netvlies\ApacheVhost\Console\Command\VhostUpdateDirCommand;
use Netvlies\ApacheVhost\System\Environment;
use Symfony\Component\Console\Application as BaseApplication;
use Netvlies\ApacheVhost\Console\Command\ReadmeCommand;
//use Netvlies\ApacheVhost\Console\Command\SelfUpdateCommand;

/**
 * @author D. Dörfel <ddorfel@netvlies.nl>
 */
class Application extends BaseApplication
{
    /**
     * @var \Netvlies\ApacheVhost\System\Environment
     */
    private $environment;

    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('Apache Vhost Utility', 0.1);

        $this->add(new ConfigCheckCommand());
        $this->add(new InitCommand());
        $this->add(new VhostUpdateDirCommand());
        $this->add(new CreateVhostCommand());
        $this->add(new ReadmeCommand());
//        $this->add(new SelfUpdateCommand());

        $this->environment = new Environment();
    }

    /**
     * @return \Netvlies\ApacheVhost\System\Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getLongVersion()
    {
        return parent::getLongVersion().' by <comment>Danny Dörfel</comment>';
    }
}
