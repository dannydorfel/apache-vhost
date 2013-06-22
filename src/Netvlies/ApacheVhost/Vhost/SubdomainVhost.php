<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-08 10:46
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Vhost;

/**
 * Class SubdomainVhost
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\Vhost
 */
use Netvlies\ApacheVhost\Finder\DocumentRootFinder;

class SubdomainVhost
{
    /**
     * @var \SplFileInfo
     */
    private $fileInfo;
    /**
     * @var
     */
    private $serverName;

    /**
     * @param $serverName
     * @param \SplFileInfo $fileInfo
     * @param array $options
     */
    public function __construct($serverName, \SplFileInfo $fileInfo, array $options = array())
    {
        $this->fileInfo = $fileInfo;
        $this->serverName = $serverName;
        $this->options = $options;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        // Checkt of de host valide is
        preg_match('#[^a-z0-9\.\-]#i', $this, $matches);
        return count($matches) > 0 ? false : true;
    }

    public function process($config)
    {
        $serverName = $this->serverName;

        $adminEmail = 'webmaster@localhost';

        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../Resources/views/');
        $twig = new \Twig_Environment($loader);

        $documentRootFinder = new DocumentRootFinder();
        $documentRoot = $documentRootFinder->find($this->fileInfo->getRealPath());

        $virtualHost = $this->getNameVirtualHost(array());
//        $directories = $this->getDirectoryDeclaration($config);

        $vars = compact('virtualHost', 'serverName', 'adminEmail', 'documentRoot', 'hostName');

        $output = $twig->render('vhost.twig', $vars);
        return $output;
    }

    protected function getNameVirtualHost($config)
    {
        $nameVirtualHost = isset($config['nameVirtualHost']) ? $config['nameVirtualHost'] : '*';
        $port = isset($config['port']) ? $config['port'] : '80';

        return "$nameVirtualHost:$port";
    }

    protected function getDirectoryDeclaration($config)
    {
        $directories = isset($config['directories']) && is_array($config['directories'])
            ? $config['directories'] : array();

        $directoryDeclarations = array();
        foreach ($directories as $directory) {
//            $directoryDeclarations[] = $this->getDirectoryDeclaration($directory);
        }

        return empty($directoryDeclarations) ? '' : implode(PHP_EOL, $directoryDeclarations) . PHP_EOL;
    }

    public function __toString()
    {
        return sprintf('%s.%s', $this->fileInfo->getFilename(), $this->serverName);
    }

    /**
     * @param  $serverName
     *
     * @return SubdomainVhost
     */
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;
        return $this;
    }

    /**
     * @return
     */
    public function getServerName()
    {
        return $this->serverName;
    }
}
