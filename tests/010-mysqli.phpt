--TEST--
mysqlnd_tls: a listed host is encrypted for mysqli
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
// mysqli connections to a listed host use TLS although the script never
// asks for it, whichever way the connection is opened.
//

require __DIR__ . '/config.inc';

mysqli_report(MYSQLI_REPORT_OFF);

$link = new mysqli(RIG_HOST, RIG_USER, RIG_PASS, RIG_DB, RIG_PORT);
echo rig_state($link), "\n";

$link = mysqli_connect(RIG_HOST, RIG_USER, RIG_PASS, RIG_DB, RIG_PORT);
echo rig_state($link), "\n";

echo rig_mysqli(RIG_HOST), "\n";
?>
--EXPECT--
encrypted
encrypted
encrypted
