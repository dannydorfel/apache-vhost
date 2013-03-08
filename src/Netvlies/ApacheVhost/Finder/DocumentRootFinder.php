<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author D. Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-07 15:58
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Netvlies\ApacheVhost\Finder;

class DocumentRootFinder
{
    public function find($basePath)
    {
        if(file_exists($basePath . '/current/web')){
            // For capistrano deployments
            $basePath .= '/current/web';
        } elseif(file_exists($basePath . '/current')) {
            // For capifony deployments
            $basePath .= '/current';
        } elseif(file_exists($basePath . '/web')) {
            // For default symfony projects
            $basePath .= '/web';
        }

        return $basePath;
    }
}
