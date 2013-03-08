<?php
/*
 * (c) Netvlies Internetdiensten
 *
 * author Danny Dörfel <ddorfel@netvlies.nl>
 * date: 2013-03-08 13:52
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Netvlies\ApacheVhost\Ssl;

/**
 * Class CertificateCreator
 * @author Danny Dörfel <ddorfel@netvlies.nl>
 * @package Netvlies\ApacheVhost\Ssl
 */
class CertificateCreator
{
    /**
     * @var string
     */
    private $certificateString = "openssl req -new -key %s -x509 -out %s -days 999 -subj '/C=NL/ST=NB/L=Breda/CN=%s'";

    /**
     * @var string
     */
    private $certificatePath;

    /**
     * @param $certificatePath
     */
    public function __construct($certificatePath)
    {
        $this->certificatePath = $certificatePath;
    }

    /**
     * @param string $certificatePath
     *
     * @return CertificateCreator
     */
    public function setCertificatePath($certificatePath)
    {
        $this->certificatePath = $certificatePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getCertificatePath()
    {
        return $this->certificatePath;
    }

    /**
     * @param string $certificateString
     *
     * @return CertificateCreator
     */
    public function setCertificateString($certificateString)
    {
        $this->certificateString = $certificateString;
        return $this;
    }

    /**
     * @return string
     */
    public function getCertificateString()
    {
        return $this->certificateString;
    }

    /**
     * @param $privateKeyFile
     * @param $hostname
     *
     * @return string
     * @throws IOException
     */
    public function createCertificate($privateKeyFile, $hostname)
    {
        if (! is_writable($this->certificatePath)) {
            throw new IOException("{$this->certificatePath} is not writable for creating ssl certificates");
        }

        $certFile = "{$this->certificatePath}/$hostname.crt";
        $output = exec(sprintf($this->certificateString, $privateKeyFile, $certFile, $hostname));
        return $certFile;
    }
}
