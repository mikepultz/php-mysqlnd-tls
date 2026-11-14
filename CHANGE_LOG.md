# Changelog

## v0.1.0

- Initial release. Turns on TLS for mysqlnd connections (mysqli and PDO) to the
  hosts listed in `mysqlnd_tls.hosts`, with certificate and host name
  verification, controlled from `php.ini` or the web server configuration.
  Supports PHP 7.4 through 8.6.
