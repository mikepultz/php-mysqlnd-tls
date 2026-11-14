--TEST--
mysqlnd_tls: a server without TLS that is not listed still works
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
// The same server, when it is not listed, is reached in plaintext as
// before.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST_PLAIN), "\n";
echo rig_pdo(RIG_HOST_PLAIN), "\n";
?>
--EXPECT--
plaintext
plaintext
