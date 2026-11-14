--TEST--
mysqlnd_tls: extension loads and reports its version and dependencies
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// The extension loads, carries a semantic version, and declares the two
// modules it cannot work without.
//

var_dump(extension_loaded('mysqlnd_tls'));

$extension = new ReflectionExtension('mysqlnd_tls');

var_dump(preg_match('/^\d+\.\d+\.\d+$/', $extension->getVersion()));
var_dump($extension->getVersion() === phpversion('mysqlnd_tls'));

$dependencies = $extension->getDependencies();

var_dump($dependencies['mysqlnd']);
var_dump($dependencies['openssl']);
?>
--EXPECT--
bool(true)
int(1)
bool(true)
string(8) "Required"
string(8) "Required"
