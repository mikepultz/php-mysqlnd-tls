--TEST--
mysqlnd_tls: "*" covers every host reached over TCP
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts="*"
mysqlnd_tls.verify=0
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// A "*" in the host list covers every TCP host.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST), "\n";
echo rig_mysqli(RIG_HOST_ALT), "\n";
echo rig_pdo(RIG_HOST_ALT), "\n";
?>
--EXPECT--
encrypted
encrypted
encrypted
