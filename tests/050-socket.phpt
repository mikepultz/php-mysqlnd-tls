--TEST--
mysqlnd_tls: unix socket connections are left alone
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
// "localhost" and an empty host mean the unix socket, which there is no
// reason to encrypt: not even "*" covers it.
//

require __DIR__ . '/config.inc';

echo rig_mysqli('localhost', 0, null, RIG_USER, RIG_PASS, RIG_SOCKET), "\n";
echo rig_mysqli(null, 0, null, RIG_USER, RIG_PASS, RIG_SOCKET), "\n";
echo rig_pdo('unix_socket=' . RIG_SOCKET), "\n";
?>
--EXPECT--
plaintext
plaintext
plaintext
