--TEST--
mysqlnd_tls: a script cannot change the settings
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=db.test
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// Every setting is PHP_INI_SYSTEM, so the policy set by the administrator
// cannot be changed from application code.
//

var_dump(ini_set('mysqlnd_tls.enabled', '0'));
var_dump(ini_set('mysqlnd_tls.hosts', ''));
var_dump(ini_set('mysqlnd_tls.verify', '0'));
var_dump(ini_set('mysqlnd_tls.ca', '/nonexistent'));
var_dump(ini_set('mysqlnd_tls.capath', '/nonexistent'));

var_dump(ini_get('mysqlnd_tls.enabled'));
var_dump(ini_get('mysqlnd_tls.hosts'));

foreach (ini_get_all('mysqlnd_tls') as $name => $entry) {
    echo $name, ' ', ($entry['access'] === INI_SYSTEM) ? 'system' : 'other', "\n";
}
?>
--EXPECT--
bool(false)
bool(false)
bool(false)
bool(false)
bool(false)
string(1) "1"
string(7) "db.test"
mysqlnd_tls.ca system
mysqlnd_tls.capath system
mysqlnd_tls.enabled system
mysqlnd_tls.hosts system
mysqlnd_tls.verify system
