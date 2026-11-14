--TEST--
mysqlnd_tls: an application's client certificate is kept
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
// The account requires a client certificate. The application's key and
// certificate are kept while its CA settings are replaced.
//

require __DIR__ . '/config.inc';

// the password alone is refused
echo rig_mysqli(RIG_HOST, 0, null, 'x509', 'x509pass'), "\n";

$init = function (mysqli $_link): void {
    $_link->ssl_set(RIG_CERTS . '/client.key', RIG_CERTS . '/client.pem', RIG_CERTS . '/other-ca.pem', '', '');
};

echo rig_mysqli(RIG_HOST, 0, $init, 'x509', 'x509pass'), "\n";

$options = [
    rig_pdo_attr('SSL_KEY')  => RIG_CERTS . '/client.key',
    rig_pdo_attr('SSL_CERT') => RIG_CERTS . '/client.pem',
];

echo rig_pdo(RIG_HOST, $options, 'x509', 'x509pass'), "\n";
?>
--EXPECT--
error 1045
encrypted
encrypted
