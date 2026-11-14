--TEST--
mysqlnd_tls: a listed server without TLS is refused, with no plaintext fallback
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=plain.test
mysqlnd_tls.verify=0
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// The server does not offer TLS. The connection is refused instead of
// falling back to plaintext.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST_PLAIN), "\n";
echo rig_pdo(RIG_HOST_PLAIN), "\n";
?>
--EXPECT--
error 2006
error 2006
