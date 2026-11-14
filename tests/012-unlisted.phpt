--TEST--
mysqlnd_tls: a host that is not listed is left alone
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
// Only the listed host is touched: the same server reached by another name
// stays as the script asked for it, which is plaintext here.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST), "\n";
echo rig_mysqli(RIG_HOST_ALT), "\n";
echo rig_pdo(RIG_HOST_ALT), "\n";
?>
--EXPECT--
encrypted
plaintext
plaintext
