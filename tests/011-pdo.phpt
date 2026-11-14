--TEST--
mysqlnd_tls: a listed host is encrypted for PDO
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
// PDO connections to a listed host use TLS although the script never asks
// for it.
//

require __DIR__ . '/config.inc';

echo rig_pdo(RIG_HOST), "\n";
?>
--EXPECT--
encrypted
