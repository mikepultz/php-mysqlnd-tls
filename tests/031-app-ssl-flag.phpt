--TEST--
mysqlnd_tls: an application that already asks for TLS still works
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
// An application that sets the TLS flag itself (WordPress's
// MYSQL_CLIENT_FLAGS, for example) does not conflict with the policy.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST, MYSQLI_CLIENT_SSL), "\n";
?>
--EXPECT--
encrypted
