--TEST--
mysqlnd_tls: persistent connections are encrypted too
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
// A persistent connection is opened through the same hook.
//

require __DIR__ . '/config.inc';

echo rig_mysqli('p:' . RIG_HOST), "\n";
echo rig_pdo(RIG_HOST, [PDO::ATTR_PERSISTENT => true]), "\n";
?>
--EXPECT--
encrypted
encrypted
