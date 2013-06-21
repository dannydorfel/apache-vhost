<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-08 10:24
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Config;

use Netvlies\ApacheVhost\System\Environment;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BaseConfig
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\Config
 */
class BaseConfig
{
    protected $configDir;
    protected $vhostsDir;
    protected $hostname;
    protected $sitesDir = "/sites.d";
    protected $sslSitesDir = "/sslsites.d";
    protected $sslKeyDir = "/ssl-files/private";
    protected $sslCertificatesDir = "/ssl-files/certs";

    /**
     * @param array $config
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config = array())
    {
        if (empty($config)) {
            return;
        }

        foreach (array_keys(get_object_vars($this)) as $property) {
            if (! isset($config[$property])) {
                throw new \InvalidArgumentException("Property $property not found in provided config");
            }
            $this->$property = $config[$property];
        }
    }

    /**
     * @param array $config
     * @return DirectoryConfig
     */
    public static function fromArray(array $config)
    {
        return new self($config);
    }

    /**
     * @param string $file
     * @return DirectoryConfig
     */
    public static function fromYmlFile($file)
    {
        return self::fromArray(Yaml::parse(file_get_contents($file)));
    }

    /**
     * @param Environment $environment
     * @return DirectoryConfig
     */
    public static function fromEnvironmentDefaults(Environment $environment)
    {
        $home = $environment->getHome($environment->getCurrentUser());
        $file = realpath($home) . '/.httpd/directory_config.yml';

        return self::fromYmlFile($file);
    }

    /**
     * @param mixed $hostname
     * @return DirectoryConfig
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param $configDir
     *
     * @return $this
     */
    public function setConfigDir($configDir)
    {
        $slash = DIRECTORY_SEPARATOR;
        $configDir = $slash . trim($configDir, $slash);
        $this->configDir = $configDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfigDir()
    {
        return $this->configDir;
    }

    /**
     * @param $vhostsDir
     *
     * @return $this
     */
    public function setVhostsDir($vhostsDir)
    {
        $this->vhostsDir = $vhostsDir;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVhostsDir()
    {
        return $this->vhostsDir;
    }

    /**
     * @return string
     */
    public function getSitesDir()
    {
        return $this->configDir . $this->sitesDir;
    }

    /**
     * @return string
     */
    public function getSslSitesDir()
    {
        return $this->configDir . $this->sslSitesDir;
    }

    /**
     * @return string
     */
    public function getSslKeyDir()
    {
        return $this->configDir . $this->sslKeyDir;
    }

    /**
     * @return string
     */
    public function getSslCertificatesDir()
    {
        return $this->configDir . $this->sslCertificatesDir;
    }

    public function toArray()
    {
        return array(
            'configDir'             => $this->configDir,
            'vhostsDir'             => $this->vhostsDir,
            'sitesDir'              => $this->sitesDir,
            'sslSitesDir'           => $this->sslSitesDir,
            'sslKeyDir'             => $this->sslKeyDir,
            'sslCertificatesDir'    => $this->sslCertificatesDir
        );
    }
}
