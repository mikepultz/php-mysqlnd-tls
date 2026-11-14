--TEST--
mysqlnd_tls: the configured CA replaces the application's
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=db.test
mysqlnd_tls.ca={PWD}/rig/certs/ca.pem
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// An application's own CA settings are replaced by the configured ones, so
// a wrong CA in the application cannot break the connection.
//

require __DIR__ . '/config.inc';

$init = function (mysqli $_link): void {
    $_link->ssl_set('', '', RIG_CERTS . '/other-ca.pem', '', '');
};

echo rig_mysqli(RIG_HOST, 0, $init), "\n";

$init = function (mysqli $_link): void {
    $_link->ssl_set('', '', '', '/nonexistent', '');
};

echo rig_mysqli(RIG_HOST, 0, $init), "\n";

echo rig_pdo(RIG_HOST, [rig_pdo_attr('SSL_CA') => RIG_CERTS . '/other-ca.pem']), "\n";
?>
--EXPECT--
encrypted
encrypted
encrypted
