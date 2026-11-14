--TEST--
mysqlnd_tls: without a CA setting the system trust store decides
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=db.test
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// With neither mysqlnd_tls.ca nor mysqlnd_tls.capath, verification falls
// back to the system trust store, which does not know the rig's CA.
//

require __DIR__ . '/config.inc';

echo rig_mysqli(RIG_HOST), "\n";
echo rig_pdo(RIG_HOST), "\n";
?>
--EXPECT--
error 2002
error 2002
