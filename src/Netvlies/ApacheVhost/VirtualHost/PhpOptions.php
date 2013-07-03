<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-13 15:06
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Vhost;

/**
 * Class PhpOptions
 * @package Netvlies\ApacheVhost\Vhost
 */
class PhpOptions
{
    /**
     * @var array
     */
    private $phpValues = array();
    /**
     * @var array
     */
    private $phpFlags = array();
    /**
     * @var array
     */
    private $phpAdminValues = array();
    /**
     * @var array
     */
    private $phpAdminFlags = array();

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        foreach (array('phpValues', 'phpFlags', 'phpAdminValues', 'phpAdminFlags') as $option) {
            if (isset($options[$option]) && is_array($options[$option])) {
                $this->$option = $options[$option];
            }
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $options = array();

        foreach (array('phpValues', 'phpFlags', 'phpAdminValues', 'phpAdminFlags') as $option) {
            $options[$option] = $this->$option;
        }

        return $options;
    }

    /**
     * @param array $phpAdminFlags
     */
    public function setPhpAdminFlags(array $phpAdminFlags)
    {
        $this->phpAdminFlags = $phpAdminFlags;
    }

    /**
     * @return array
     */
    public function getPhpAdminFlags()
    {
        return $this->phpAdminFlags;
    }

    /**
     * @param array $phpAdminValues
     */
    public function setPhpAdminValues(array $phpAdminValues)
    {
        $this->phpAdminValues = $phpAdminValues;
    }

    /**
     * @return array
     */
    public function getPhpAdminValues()
    {
        return $this->phpAdminValues;
    }

    /**
     * @param array $phpFlags
     */
    public function setPhpFlags(array $phpFlags)
    {
        $this->phpFlags = $phpFlags;
    }

    /**
     * @return array
     */
    public function getPhpFlags()
    {
        return $this->phpFlags;
    }

    /**
     * @param array $phpValues
     */
    public function setPhpValues($phpValues)
    {
        $this->phpValues = $phpValues;
    }

    /**
     * @return array
     */
    public function getPhpValues()
    {
        return $this->phpValues;
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function setPhpValue($name, $value)
    {
        $this->phpValues[$name] = $value;
        return this;
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function setPhpFlag($name, $value)
    {
        $this->phpFlags[$name] = $value;
        return this;
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function setPhpAdminValue($name, $value)
    {
        $this->phpAdminValues[$name] = $value;
        return this;
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function setPhpAdminFlag($name, $value)
    {
        $this->phpAdminFlags[$name] = $value;
        return this;
    }
}
