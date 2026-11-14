--TEST--
mysqlnd_tls: the host list ignores case, spaces and a trailing dot
--SKIPIF--
<?php require __DIR__ . '/config.inc'; rig_skipif(); ?>
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=" other.test , DB.Test ,,"
mysqlnd_tls.verify=0
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// Entries are separated by commas, may be padded with spaces, and match
// without regard to case. A trailing dot on the host is ignored.
//

require __DIR__ . '/config.inc';

echo rig_mysqli('db.test'), "\n";
echo rig_mysqli('DB.TEST'), "\n";
echo rig_mysqli('db.test.'), "\n";
echo rig_mysqli(RIG_HOST_ALT), "\n";
?>
--EXPECT--
encrypted
encrypted
encrypted
plaintext
