<?php
/**
 * (c) Netvlies Internetdiensten
 *
 * author D. Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-07 14:09
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Util;

use Symfony\Component\Finder\Finder;

/**
 * The Compiler class compiles the Netvlies Apache Vhost utility.
 *
 * @author D. Dörfel <ddorfel@netvlies.nl>
 */
class Compiler
{
    public function compile($pharFile = 'apache-vhost.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'apache-vhost.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        // CLI Component files
        foreach ($this->getFiles() as $file) {
            $path = str_replace(__DIR__.'/', '', $file);
            $phar->addFromString($path, file_get_contents($file));
        }
        $this->addApacheVhost($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        // $phar->compressFiles(\Phar::GZ);

        unset($phar);

        chmod($pharFile, 0777);
    }

    /**
     * Remove the shebang from the file before add it to the PHAR file.
     *
     * @param \Phar $phar PHAR instance
     */
    protected function addApacheVhost(\Phar $phar)
    {
        $content = file_get_contents(__DIR__ . '/../../../../bin/apache-vhost');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);

        $phar->addFromString('apache-vhost', $content);
    }

    protected function getStub()
    {
        return "#!/usr/bin/env php\n<?php Phar::mapPhar('apache-vhost.phar'); require 'phar://apache-vhost.phar/apache-vhost'; __HALT_COMPILER();";
    }

    protected function getLicense()
    {
        return '
    /*
     * This file is part of the Netvlies ApacheVhost utility.
     *
     * (c) Netvlies Internetdiensten
     *
     * This source file is subject to the MIT license that is bundled
     * with this source code in the file LICENSE.
     */';
    }

    protected function getFiles()
    {
        $iterator = Finder::create()->files()->exclude('Tests')->name('*.php')->in(array('vendor', 'src'));

        return array_merge(array('LICENSE'), iterator_to_array($iterator));
    }
}
