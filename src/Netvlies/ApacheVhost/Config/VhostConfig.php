<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 3-7-13 20:54
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Config;

use Symfony\Component\Yaml\Yaml;

class VhostConfig
{
    protected $config = array();

    /**
     * @param array $config
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config = array())
    {
        if (empty($config)) {
            return;
        }

        $this->config = $config;
    }

    /**
     * @param array $config
     * @return VhostConfig
     */
    public static function fromArray(array $config)
    {
        return new self($config);
    }

    /**
     * @param string $file
     * @return BaseConfig
     */
    public static function fromYmlFile($file)
    {
        return self::fromArray(Yaml::parse(file_get_contents($file)));
    }

    /**
     * @param $filename
     */
    public function saveToFile($filename)
    {
        file_put_contents($filename, Yaml::dump($this->config));
    }

    /**
     * @param $vhost
     * @return bool
     */
    public function has($vhost)
    {
        return isset($this->config[$vhost]);
    }

    /**
     * @param $vhost
     * @return mixed
     */
    public function get($vhost)
    {
        return $this->config[$vhost];
    }

    /**
     * @param $vhost
     * @param $config
     * @return $this
     */
    public function set($vhost, $config)
    {
        $this->config[$vhost] = $config;
        return $this;
    }
}
