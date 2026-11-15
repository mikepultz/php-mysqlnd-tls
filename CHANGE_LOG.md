# Changelog

## v1.0.0

- Initial release. Turns on TLS for mysqlnd connections (mysqli and PDO) to the
  hosts listed in `mysqlnd_tls.hosts`, with certificate and host name
  verification, controlled from `php.ini` or the web server configuration.
  Supports PHP 7.4 through 8.6.
- Pre-built binaries for PHP 7.4 through 8.6 on Linux (glibc, x86_64 and arm64),
  installed by PIE with no compiler.
