# php-mysqlnd-tls

[![CI](https://github.com/mikepultz/php-mysqlnd-tls/actions/workflows/ci.yml/badge.svg)](https://github.com/mikepultz/php-mysqlnd-tls/actions/workflows/ci.yml)
[![Packagist Version](https://img.shields.io/packagist/v/mikepultz/php-mysqlnd-tls)](https://packagist.org/packages/mikepultz/php-mysqlnd-tls)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)
[![License](https://img.shields.io/badge/license-BSD--3--Clause-blue)](LICENSE)

A PHP extension that turns on **TLS for MySQL connections** from `php.ini`,
with no change to the application. It covers everything that connects through
mysqlnd, which means both `mysqli` and `PDO`.

```ini
mysqlnd_tls.enabled = On
mysqlnd_tls.hosts   = "db.example.com"
```

With those two lines every connection PHP opens to `db.example.com` is
encrypted and the server's certificate is verified, whether or not the
application asks for it. Connections to any other host are left exactly as
they were.

## Why

The MySQL protocol lets only the client start TLS: the server can refuse a
plaintext connection, but it cannot upgrade one. PHP in turn has no setting for
it. An application has to ask for TLS itself, with `MYSQLI_CLIENT_SSL`,
`mysqli::ssl_set()` or the `PDO::MYSQL_ATTR_SSL_*` options, and one that asks
without also setting a CA gets encryption without any verification.

That leaves an administrator who wants the database traffic encrypted with
editing every application. On a shared host, where the applications belong to
customers and change all the time, that is not practical. This extension moves
the decision to the place the administrator controls.

## Requirements

- **PHP 7.4 or later**, thread safe or not. One source tree covers every
  version; it is built and tested on 7.4 through 8.6.
- **mysqlnd**, with `mysqli` and/or `pdo_mysql` built on it. That is the default
  everywhere, and the only option for `mysqli` since PHP 8.2.
- **The openssl extension**: mysqlnd's TLS runs through it. PHP refuses to load
  `mysqlnd_tls` without it.
- **Linux.** Other Unix-like systems should work but are not tested. Windows is
  not supported yet.

## Installing

### With PIE (recommended)

```sh
pie install mikepultz/php-mysqlnd-tls
```

Each release carries pre-built binaries for PHP 7.4 through 8.6 on x86_64 and
arm64, for Linux with glibc and the non-thread-safe PHP that distributions
ship. [PIE](https://github.com/php/pie) installs the one that matches, so no
compiler is needed. They are built on AlmaLinux 9 against Remi's PHP, and each
one passes the test suite on Remi's PHP and on the official PHP image before it
is published: a module built against one distribution's PHP loads in another's
of the same minor version.

Where no binary matches (musl, a thread-safe PHP), PIE builds from source,
which needs a compiler and the PHP development files; those include the mysqlnd
headers. To build from source even when a binary exists:

```sh
pie install mikepultz/php-mysqlnd-tls --enable-mysqlnd-tls
```

PIE installs into the PHP it finds on the `PATH`. For a PHP that lives
elsewhere, such as one of Remi's parallel `php83` packages, put it first:

```sh
PATH=/opt/remi/php83/root/usr/bin:$PATH pie install mikepultz/php-mysqlnd-tls
```

PIE itself runs on PHP 8.1 or later. Where the only PHP is older, use PIE's
standalone executable, which carries its own.

### From source

```sh
phpize
./configure
make
sudo make install
```

Then load it with `extension=mysqlnd_tls` in an ini file that is read **after**
the one that loads mysqlnd (PIE names the file `40-mysqlnd_tls.ini` for that
reason). Where mysqlnd is a shared module, as in the Red Hat family's packages,
loading `mysqlnd_tls` first fails with an `undefined symbol` warning and PHP
carries on without it.

Check that it is there with `php -m`: a PHP that did not load the extension
runs normally and applies no policy. Loading it changes nothing by itself
either: it is off until `mysqlnd_tls.enabled` is set.

## Settings

| Setting | Default | Meaning |
| --- | --- | --- |
| `mysqlnd_tls.enabled` | `Off` | The switch. |
| `mysqlnd_tls.hosts` | empty | The database hosts the policy applies to, separated by commas. `*` means every host reached over TCP. |
| `mysqlnd_tls.verify` | `On` | Verify the server's certificate and that it was issued for the host name. |
| `mysqlnd_tls.ca` | empty | A CA bundle file to verify against. |
| `mysqlnd_tls.capath` | empty | An OpenSSL hashed directory of CA certificates to verify against. |

A host is matched against the name the application connects to, without regard
to case. List every name in use: `db.example.com` does not cover the same
server reached by its IP address.

With verification on and neither `mysqlnd_tls.ca` nor `mysqlnd_tls.capath` set,
PHP falls back to its own `openssl.cafile` and `openssl.capath` settings, and
then to the system trust store. That is the right setup for a server
certificate from a public CA. A hashed directory is the cheapest to verify
against, since only the one CA needed is read; a bundle file is parsed in full
on every connection.

### Where to set them

Every setting is `PHP_INI_SYSTEM`, so it can be set in `php.ini` or by the
administrator in the web server configuration, and nowhere else. Application
code cannot change it: `ini_set()`, `.htaccess` and `.user.ini` are all
refused.

Server wide, in `php.ini`:

```ini
mysqlnd_tls.enabled = On
mysqlnd_tls.hosts   = "db.example.com,db-replica.example.com"
mysqlnd_tls.capath  = /etc/ssl/certs
```

Per site with Apache and mod_php, in the virtual host:

```apache
<VirtualHost *:80>
    ServerName www.example.com
    php_admin_flag mysqlnd_tls.enabled on
</VirtualHost>
```

Per pool with PHP-FPM:

```ini
php_admin_flag[mysqlnd_tls.enabled] = on
```

Setting the host list server wide and the switch per site or per pool is a
convenient way to roll the change out a few sites at a time.

## Behaviour

**A listed host** is always connected to with TLS. If the server does not offer
TLS the connection is refused (error 2006). There is no fallback to plaintext.

With `mysqlnd_tls.verify = On`:

- the certificate must chain to the configured trust store and carry the host
  name the application connected to; otherwise the connection is refused
  (error 2002);
- the configured trust store replaces any CA the application set, so an
  application can neither break the connection with a wrong CA nor widen the
  trust with its own;
- an application that asks for no verification
  (`MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT`,
  `PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false`) is verified anyway.

With `mysqlnd_tls.verify = Off` the connection is encrypted but the certificate
is not checked, and that applies just as firmly: an application that asks for
verification itself, with its own CA or the verify flag, is not verified
either. Use this for a server whose certificate cannot be verified, such as one
reached by IP address.

Either way the setting decides, never the application, so every application
connecting to a listed host gets the same result.

In both cases an application's client certificate and key are kept, so
accounts that require one keep working, and an application that already asks
for TLS (WordPress with `MYSQL_CLIENT_FLAGS`, for example) is not disturbed.

**Any other host** is not touched: it gets TLS only if the application asks,
with the application's own settings.

**Unix socket connections** (`localhost`, or no host at all) are never touched,
not even by `*`.

### Checking a connection

The session's own status shows whether it is encrypted:

```php
$row = $mysqli->query("show session status like 'Ssl_cipher'")->fetch_row();

echo ($row[1] === '') ? 'plaintext' : 'encrypted with ' . $row[1];
```

## What it does not do

- **It is not enforcement.** It makes PHP ask for TLS; it cannot stop a client
  that does not load it. Anyone who can start PHP with their own settings (the
  command line with `-n` or `-d`) connects however they like. Once every client
  is on TLS, enforce it on the server, per account with
  `alter user ... require ssl` or for everyone with
  `require_secure_transport = ON`.
- **It only sees mysqlnd.** A `pdo_mysql` built against libmysqlclient instead
  bypasses it.
- **It acts when a connection is opened.** A persistent connection that is
  reused keeps the mode it was opened with, so a change of settings reaches it
  only when it is opened again.

Two things to keep in mind when turning it on:

- The server's certificate is now on the path of every request, as a web
  server's certificate is. Its renewal has to be automatic and its expiry
  monitored.
- TLS adds a handshake to every new database connection. Measure the cost for
  your own workload before enabling it everywhere.

## How it works

mysqlnd lets an extension replace the methods of its connection object.
`mysqlnd_tls` replaces `connect` with a wrapper that, for a listed host, adds
the TLS flag, sets the verification flags and the trust store through
mysqlnd's own client options, and then calls the original. Nothing else in
mysqlnd is touched, and its internal structures are never read, which is why
one source tree builds on every PHP version from 7.4 on.

## Tests

The suite in `tests/` needs two MySQL servers, one with TLS and one without,
reached by fixed host names. `tests/rig` provides them with Docker, builds the
extension inside a PHP image with warnings as errors, and runs the suite:

```sh
tests/rig/rig.sh run                 # the official PHP 8.5 image
tests/rig/rig.sh run php:7.4-cli     # any official PHP image
tests/rig/rig.sh run remi:8.3        # AlmaLinux 9 with Remi's PHP 8.3
tests/rig/rig.sh down                # stop the servers
```

A second argument names a `mysqlnd_tls.so` to test in place of building one,
which is how the release binaries are tested.
`.github/scripts/build-binary.sh` builds such a binary.

Without the rig, `make test` runs the tests that need no server and skips the
rest.

## License

BSD-3-Clause. See [LICENSE](LICENSE).
