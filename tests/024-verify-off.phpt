--TEST--
mysqlnd_tls: with verify off the connection is encrypted but not checked
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=alt.test
mysqlnd_tls.verify=0
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// mysqlnd_tls.verify=0 still forces TLS; a certificate for another name is
// accepted.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST_ALT), "\n";
echo rig_pdo(RIG_HOST_ALT), "\n";
?>
--EXPECT--
encrypted
encrypted
