--TEST--
mysqlnd_tls: with verify off an application cannot switch verification on
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
// mysqlnd_tls.verify=0 applies as firmly as verify on: an application that
// asks for verification itself is not verified either. The certificate is
// for another name, so any verification would refuse the connection.
//

require __DIR__ . '/config.inc';

// its own CA, which on its own makes mysqlnd verify
$init = function (mysqli $_link): void {
    $_link->ssl_set('', '', RIG_CERTS . '/ca.pem', '', '');
};

echo rig_mysqli(RIG_HOST_ALT, 0, $init), "\n";

// its own CA, and the verify flag
echo rig_mysqli(RIG_HOST_ALT, MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_VERIFY_SERVER_CERT, $init), "\n";

// the verify option on the handle
$init = function (mysqli $_link): void {
    $_link->ssl_set('', '', RIG_CERTS . '/ca.pem', '', '');
    $_link->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
};

echo rig_mysqli(RIG_HOST_ALT, 0, $init), "\n";

$options = [
    rig_pdo_attr('SSL_CA')                 => RIG_CERTS . '/ca.pem',
    rig_pdo_attr('SSL_VERIFY_SERVER_CERT') => true,
];

echo rig_pdo(RIG_HOST_ALT, $options), "\n";
?>
--EXPECT--
encrypted
encrypted
encrypted
encrypted
