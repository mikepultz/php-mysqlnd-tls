--TEST--
mysqlnd_tls: a certificate for another name is refused
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=alt.test
mysqlnd_tls.ca={PWD}/rig/certs/ca.pem
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// The server's certificate is issued for db.test only, so the same server
// reached as alt.test fails verification and the connection is refused.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST_ALT), "\n";
echo rig_pdo(RIG_HOST_ALT), "\n";
?>
--EXPECT--
error 2002
error 2002
