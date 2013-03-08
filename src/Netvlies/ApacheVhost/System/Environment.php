<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-08 13:57
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\System;

/**
 * Class Environment
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\System
 */
class Environment
{
    /**
     * @return string
     */
    public function getCurrentUser()
    {
        return trim(`whoami`);
    }

    /**
     * @param $username
     *
     * @return string
     */
    public function getHome($username)
    {
        return trim(`echo ~$username`);
    }

    /**
     * @return string
     */
    public function getSystemHostName()
    {
        return trim(`hostname -f`);
    }
}
