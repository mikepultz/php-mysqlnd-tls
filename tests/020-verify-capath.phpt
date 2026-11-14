--TEST--
mysqlnd_tls: the certificate verifies against a hashed CA directory
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=db.test
mysqlnd_tls.capath={PWD}/rig/certs/capath
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// mysqlnd_tls.capath works in place of mysqlnd_tls.ca.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST), "\n";
echo rig_pdo(RIG_HOST), "\n";
?>
--EXPECT--
encrypted
encrypted
