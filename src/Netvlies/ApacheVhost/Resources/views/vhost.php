<?php
/**
 * (c) Netvlies Internetdiensten
 *
 * author D. Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-07 15:24
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>
<VirtualHost *:80>
    ServerAdmin <?php echo $adminEmail . "\n" ?>
    DocumentRoot <?php echo $documentRoot . "\n" ?>
    ServerName <?php echo $hostname . "\n" ?>
<?php
//if (file_exists($basedir.'/aliases.txt')) {
//$aliases = preg_split('/[\s]{1,99}/', trim(file_get_contents($basedir.'/aliases.txt')));
//foreach ($aliases as &$alias) {
//$alias .= '.' . $vhost;
//}
//echo "  (with aliases: ".implode(', ', $aliases).")\n";
//$textfile .= "\tServerAlias ".implode(' ', $aliases)."\n";
//}
?>
<?php if ($errorLog): ?>    ErrorLog <?php echo $errorLog . "\n"; endif ?>
<?php if ($transferLog): ?>    TransferLog <?php echo $transferLog . "\n"; endif ?>
    <Directory <?php echo $documentRoot ?>>
        AllowOverride All
<?php if ($xDebugProfiler): ?>php_admin_flag xdebug.profiler_enable_trigger 1<?php endif ?>
<?php if ($xDebugProfilerOutputDir): ?>php_admin_value xdebug.profiler_output_dir <?php echo $xDebugProfilerOutputDir; endif ?>
<?php if ($xDebugTraceOutputDir): ?>php_admin_value xdebug.trace_output_dir <?php echo $xDebugTraceOutputDir; endif ?>
    </Directory>
</VirtualHost>
