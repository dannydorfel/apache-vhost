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

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;
use \InvalidArgumentException;

/**
 * Class DirectoryConfig
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\Config
 */
class DirectoryConfig
{
    protected $configDir;
    protected $vhostsDir;
    protected $sitesDir = "/sites.d";
    protected $sslSitesDir = "/sslsites.d";
    protected $sslKeyDir = "/ssl-files/private";
    protected $sslCertificatesDir = "/ssl-files/certs";

    /**
     * @param array $config
     * @throws InvalidArgumentException
     */
    public function __construct(array $config = array())
    {
        if (empty($config)) {
            return;
        }

        foreach (array_keys(get_object_vars($this)) as $property) {
            if (! isset($config[$property])) {
                throw new InvalidArgumentException("Property $property not found in provided config");
            }
            $this->$property = $config[$property];
        }
    }

    /**
     * @param DialogHelper    $dialog
     * @param OutputInterface $output
     *
     * @return bool
     */
    public function ensureCreated(DialogHelper $dialog, OutputInterface $output)
    {
        $dialogQuestion = "<question>The directory %s does not exist yet. Create it?</question> (Y/N) ";

        $confirm = function ($dir) use ($dialog, $output, $dialogQuestion) {
            return $dialog->askConfirmation($output, sprintf($dialogQuestion, $dir), false);
        };

        foreach (array('getConfigDir', 'getVhostsDir') as $callable) {
            if (! $this->ensureDirectory($this->$callable(), $confirm)) {
                return false;
            }
        }

        foreach (array('getSslSitesDir', 'getSslKeyDir', 'getSitesDir') as $callable) {
            if (! $this->ensureDirectory($this->$callable())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param          $dir
     * @param callable $dialogCallback
     *
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
