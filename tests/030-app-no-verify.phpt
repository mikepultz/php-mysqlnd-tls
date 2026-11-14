--TEST--
mysqlnd_tls: an application cannot switch verification off
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=alt.test
mysqlnd_tls.ca={PWD}/rig/certs/ca.pem
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// With verify on, an application that asks for an unverified connection is
// still verified: the certificate for another name is refused.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST_ALT, MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT), "\n";

$init = function (mysqli $_link): void {
    $_link->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
};

echo rig_mysqli(RIG_HOST_ALT, MYSQLI_CLIENT_SSL, $init), "\n";

echo rig_pdo(RIG_HOST_ALT, [rig_pdo_attr('SSL_VERIFY_SERVER_CERT') => false]), "\n";
?>
--EXPECT--
error 2002
error 2002
error 2002
