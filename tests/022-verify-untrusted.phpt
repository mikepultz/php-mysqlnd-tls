--TEST--
mysqlnd_tls: a certificate from a CA that is not trusted is refused
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
// The configured CA did not issue the server's certificate.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST), "\n";
echo rig_pdo(RIG_HOST), "\n";
?>
--EXPECT--
error 2002
error 2002
