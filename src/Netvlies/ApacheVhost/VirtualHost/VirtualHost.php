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
namespace Netvlies\ApacheVhost\VirtualHost;

use Netvlies\ApacheVhost\Finder\DocumentRootFinder;
use Netvlies\ApacheVhost\Vhost\PhpOptions;

/**
 * Class VirtualHost
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\Vhost
 */
class VirtualHost
{
    /**
     * @var array
     */
    protected $defaultPhpOptions = array(
        'phpValues' => array(),
        'phpAdminValues' => array(
//            'xdebug.profiler_output_dir' => '#outputdir#',
//            'xdebug.trace_output_dir' => '#outputdir#',
        ),
        'phpFlags' => array(),
        'phpAdminFlags' => array(
//            'xdebug.profiler_enable_trigger' => 1
        ),
    );

    /**
     * @var \SplFileInfo
     */
    private $fileInfo;
    /**
     * @var string
     */
    private $virtualHost = '*:80';
    /**
     * @var string
     */
    private $documentRoot;
    /**
     * @var string
     */
    private $webmasterEmail = 'webmaster@localhost';
    /**
     * @var string
     */
    private $serverName;
    /**
     * @var array
     */
    private $serverAliases = array();
    /**
     * @var array
     */
    private $directoryOptions = array(
        'Options' => 'Indexes FollowSymLinks',
        'AllowOverride' => 'All',
    );

    /**
     * @var \Netvlies\ApacheVhost\Vhost\PhpOptions
     */
    private $phpOptions;

    /**
     * @param $serverName
     * @param \SplFileInfo $fileInfo
     */
    public function __construct($serverName, \SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
        $this->serverName = $serverName;

        $this->phpOptions = new PhpOptions($this->defaultPhpOptions);
    }

    /**
     * @param $serverName
     * @return $this
     */
    public function setServerName($serverName)
    {
        $this->serverName = $serverName;
        return $this;
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @param \SplFileInfo $fileInfo
     */
    public function setFileInfo(\SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
    }

    /**
     * @return \SplFileInfo
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * @param array $directoryOptions
     */
    public function setDirectoryOptions($directoryOptions)
    {
        $this->directoryOptions = $directoryOptions;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setDirectoryOption($name, $value)
    {
        $this->directoryOptions[$name] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getDirectoryOptions()
    {
        return $this->directoryOptions;
    }

    /**
     * @param string $documentRoot
     */
    public function setDocumentRoot($documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    /**
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * @param PhpOptions $phpOptions
     * @return $this
     */
    public function setPhpOptions(PhpOptions $phpOptions)
    {
        $this->phpOptions = $phpOptions;
        return $this;
    }

    /**
     * @return PhpOptions
     */
    public function getPhpOptions()
    {
        return $this->phpOptions;
    }

    /**
     * @param array $serverAliases
     */
    public function setServerAliases($serverAliases)
    {
        $this->serverAliases = $serverAliases;
    }

    /**
     * @return array
     */
    public function getServerAliases()
    {
        return $this->serverAliases;
    }

    /**
     * @param string $virtualHost
     */
    public function setVirtualHost($virtualHost)
    {
        $this->virtualHost = $virtualHost;
    }

    /**
     * @return string
     */
    public function getVirtualHost()
    {
        return $this->virtualHost;
    }

    /**
     * @param string $webmasterEmail
     */
    public function setWebmasterEmail($webmasterEmail)
    {
        $this->webmasterEmail = $webmasterEmail;
    }

    /**
     * @return string
     */
    public function getWebmasterEmail()
    {
        return $this->webmasterEmail;
    }

    public function process()
    {
        $documentRootFinder = new DocumentRootFinder();
        $this->documentRoot = $documentRootFinder->find($this->fileInfo->getRealPath());

        // todo: process serverName, filter unwanted characters
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../Resources/views/');
        $twig = new \Twig_Environment($loader);
        return $twig->render('vhost.twig', $this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'virtualHost' => $this->virtualHost,
            'serverName' => $this->serverName,
            'documentRoot' => $this->documentRoot,
            'webmasterEmail' => $this->webmasterEmail,
            'serverAliases' => $this->serverAliases,
            'directoryOptions' => $this->directoryOptions,
            'phpOptions' => $this->phpOptions,
        );
    }
}
