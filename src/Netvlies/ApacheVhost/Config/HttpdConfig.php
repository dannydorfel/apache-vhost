<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-08 17:35
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Config;

/**
 * Class VhostsConfig
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\Config
 */
class HttpdConfig
{
    /**
     * @var DirectoryConfig
     */
    protected $directoryConfig;

    /**
     * @param \Netvlies\ApacheVhost\Config\DirectoryConfig $directoryConfig
     *
     * @return HttpdConfig
     */
    public function setDirectoryConfig($directoryConfig)
    {
        $this->directoryConfig = $directoryConfig;
        return $this;
    }

    /**
     * @return \Netvlies\ApacheVhost\Config\DirectoryConfig
     */
    public function getDirectoryConfig()
    {
        return $this->directoryConfig;
    }

    public function toArray()
    {
        return array(
            'directoryConfig' => $this->directoryConfig->toArray()
        );
    }
}
