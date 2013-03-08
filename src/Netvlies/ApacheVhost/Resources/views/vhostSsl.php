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
NameVirtualHost <?php echo $hostname ?>:443
<VirtualHost <?php echo $hostname ?>:443>
    ServerAdmin <?php echo $adminEmail . "\n" ?>
    DocumentRoot <?php echo $documentRoot . "\n" ?>
    ServerName <?php echo $hostname . "\n" ?>
    <Directory <?php echo $documentRoot ?>>
        AllowOverride All
    </Directory>
    SSLEngine On
    SSLCertificateFile /etc/pki/tls/certs/<?php echo $hostname ?>.crt
    SSLCertificateKeyFile /etc/pki/tls/private/<?php echo $hostname ?>.key
</VirtualHost>
