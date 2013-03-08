<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-08 13:49
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Ssl;

/**
 * Class PrivateKeyCreator
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\Ssl
 */
class PrivateKeyCreator
{
    /**
     * @var string
     */
    private $keyString = "openssl genrsa -out %s 1024";

    /**
     * @var string
     */
    private $keyPath;

    /**
     * @param $keyPath
     */
    public function __construct($keyPath)
    {
        $this->keyPath = $keyPath;
    }

    /**
     * @param string $keyString
     *
     * @return PrivateKeyCreator
     */
    public function setKeyString($keyString)
    {
        $this->keyString = $keyString;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeyString()
    {
        return $this->keyString;
    }

    /**
     * @param string $keyPath
     *
     * @return PrivateKeyCreator
     */
    public function setKeyPath($keyPath)
    {
        $this->keyPath = $keyPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeyPath()
    {
        return $this->keyPath;
    }

    /**
     * @param $hostname
     *
     * @return string
     * @throws IOException
     */
    public function createKey($hostname)
    {
        if (! is_writable($this->keyPath)) {
            throw new IOException("{$this->keyPath} is not writable for creating ssl certificates");
        }

        $privateKeyFile = "{$this->keyPath}/$hostname.key";
        exec(sprintf($this->keyString, $privateKeyFile));
        return $privateKeyFile;
    }
}
