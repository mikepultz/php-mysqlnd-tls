--TEST--
mysqlnd_tls: the application's CA cannot widen the trust
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=db.test
mysqlnd_tls.ca={PWD}/rig/certs/other-ca.pem
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// The configured CA did not issue the server's certificate. The
// application brings the CA that did, and it is not used.
//

require __DIR__ . '/config.inc';

$init = function (mysqli $_link): void {
    $_link->ssl_set('', '', RIG_CERTS . '/ca.pem', '', '');
};

echo rig_mysqli(RIG_HOST, 0, $init), "\n";

$init = function (mysqli $_link): void {
    $_link->ssl_set('', '', '', RIG_CERTS . '/capath', '');
};

echo rig_mysqli(RIG_HOST, 0, $init), "\n";

echo rig_pdo(RIG_HOST, [rig_pdo_attr('SSL_CA') => RIG_CERTS . '/ca.pem']), "\n";
?>
--EXPECT--
error 2002
error 2002
error 2002
