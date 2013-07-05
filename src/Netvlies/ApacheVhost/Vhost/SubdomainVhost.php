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
    private $documentRoot;
    private $options;

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

        $tpl = $this->getTemplate();

        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader);

        $documentRootFinder = new DocumentRootFinder();
        $this->documentRoot = $documentRoot = $documentRootFinder->find($this->fileInfo->getRealPath());

        $virtualHost = $this->getNameVirtualHost(array());
//        $directories = $this->getDirectoryDeclaration($config);

        $options = $this->options;

        $vars = compact('virtualHost', 'serverName', 'adminEmail', 'documentRoot', 'hostName', 'options');

        $output = $twig->render($tpl, $vars);
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

    protected function getTemplate()
    {
        return <<<EOF
{# (c) Netvlies Internetdiensten

    author D. Dörfel <ddorfel@netvlies.nl>
    date: 2013-03-07 15:24

    For the full copyright and license information, please view the LICENSE
    file that was distributed with this source code. #}
<VirtualHost {{ virtualHost }}>

    ServerAdmin {{ adminEmail }}
    DocumentRoot {{ documentRoot }}
    ServerName {{ serverName }}
{% if options.serverAlias is defined %}
{% for alias in options.serverAlias %}
    ServerAlias {{ alias }}
{% endfor %}
{% endif %}
{% if errorLog is defined and errorLog %}    ErrorLog {{ errorLog }}{% endif %}
{% if transferLog is defined and transferLog %}    TransferLog {{ transferLog }}{% endif %}
{#if (file_exists(\$basedir.'/aliases.txt')) {#}
{#\$aliases = preg_split('/[\s]{1,99}/', trim(file_get_contents(\$basedir.'/aliases.txt')));#}
{#foreach (\$aliases as &\$alias) {#}
{#\$alias .= '.' . \$vhost;#}
{#}#}
{#echo "  (with aliases: ".implode(', ', \$aliases).")\n";#}
{#\$textfile .= "\tServerAlias ".implode(' ', \$aliases)."\n";#}
{#}#}

    <Directory {{ documentRoot }}>
        AllowOverride All
{#<?php if (isset(\$xDebugProfiler) && \$xDebugProfiler): ?>php_admin_flag xdebug.profiler_enable_trigger 1<?php endif ?>#}
{#<?php if (isset(\$xDebugProfilerOutputDir) && \$xDebugProfilerOutputDir): ?>php_admin_value xdebug.profiler_output_dir <?php print \$xDebugProfilerOutputDir; endif ?>#}
{#<?php if (isset(\$xDebugTraceOutputDir) && \$xDebugTraceOutputDir): ?>php_admin_value xdebug.trace_output_dir <?php print \$xDebugTraceOutputDir; endif ?>#}
</Directory>

</VirtualHost>
EOF;
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

    public function toArray()
    {
        return array_merge(
            array(
                'serverName' => $this->serverName,
                'documentRoot' => $this->documentRoot,
            ),
            $this->options
        );
    }
}
