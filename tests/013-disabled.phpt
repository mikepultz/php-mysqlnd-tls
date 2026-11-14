--TEST--
mysqlnd_tls: nothing changes while the extension is switched off
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=0
mysqlnd_tls.hosts=db.test
mysqlnd_tls.ca={PWD}/rig/certs/ca.pem
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// With mysqlnd_tls.enabled off the host list has no effect.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST), "\n";
echo rig_pdo(RIG_HOST), "\n";
?>
--EXPECT--
plaintext
plaintext
