--TEST--
mysqlnd_tls: settings default to off, verifying, with no hosts
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// Loading the extension changes nothing until it is switched on: it is off
// by default, and verification is on by default.
//

foreach (['enabled', 'hosts', 'verify', 'ca', 'capath'] as $name) {
    var_dump(ini_get('mysqlnd_tls.' . $name));
}
?>
--EXPECT--
string(1) "0"
string(0) ""
string(1) "1"
string(0) ""
string(0) ""
