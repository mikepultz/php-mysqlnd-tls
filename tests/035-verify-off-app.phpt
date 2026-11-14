--TEST--
mysqlnd_tls: with verify off the application's own verification is kept
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=alt.test
mysqlnd_tls.verify=0
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// mysqlnd_tls.verify=0 forces TLS and nothing else: an application that
// verifies with its own CA is still verified, and one that does not is not.
//

require __DIR__ . '/config.inc';

// no settings of its own: encrypted, not verified
echo rig_mysqli(RIG_HOST_ALT), "\n";

// its own CA: verified, and the certificate is for another name
$init = function (mysqli $_link): void {
    $_link->ssl_set('', '', RIG_CERTS . '/ca.pem', '', '');
};

echo rig_mysqli(RIG_HOST_ALT, 0, $init), "\n";

// its own CA, and it asks not to verify
echo rig_mysqli(RIG_HOST_ALT, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT, $init), "\n";
?>
--EXPECT--
encrypted
error 2002
encrypted
