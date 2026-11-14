--TEST--
mysqlnd_tls: phpinfo() shows the extension and its settings
--INI--
mysqlnd_tls.enabled=1
mysqlnd_tls.hosts=db.test
--FILE--
<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// The phpinfo() section lists the version and every setting.
//

ob_start();
(new ReflectionExtension('mysqlnd_tls'))->info();
$info = ob_get_clean();

$lines = [
    'mysqlnd_tls support => enabled',
    'Extension version => ' . phpversion('mysqlnd_tls'),
    'mysqlnd_tls.enabled => On => On',
    'mysqlnd_tls.hosts => db.test => db.test',
    'mysqlnd_tls.verify => On => On',
    'mysqlnd_tls.ca => no value => no value',
    'mysqlnd_tls.capath => no value => no value',
];

foreach ($lines as $line) {
    var_dump(strpos($info, $line) !== false);
}
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
