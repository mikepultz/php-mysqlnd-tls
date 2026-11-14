<?php declare(strict_types=1);

//
// Copyright (c) 2026, Mike Pultz All rights reserved.
//
// Shared by the tests that need the rig in tests/rig: two MySQL servers,
// reached by fixed names so the --INI-- sections can list them.
//

// the TLS server, by the name its certificate is issued for
const RIG_HOST = 'db.test';

// the same server, by a name its certificate does not carry
const RIG_HOST_ALT = 'alt.test';

// a server with TLS turned off
const RIG_HOST_PLAIN = 'plain.test';

const RIG_PORT   = 3306;
const RIG_DB     = 'test';
const RIG_USER   = 'tls';
const RIG_PASS   = 'tlspass';
const RIG_SOCKET = '/var/run/mysqld/mysqld.sock';
const RIG_CERTS  = __DIR__ . '/rig/certs';

//
// for --SKIPIF--: skips unless the MySQL extensions are loaded and the rig's
// names resolve. A name lookup, not a connection, so the servers never count
// an aborted handshake.
//
function rig_skipif(): void
{
    foreach (['mysqlnd_tls', 'mysqli', 'pdo_mysql'] as $extension) {
        if (extension_loaded($extension) === false) {
            die('skip ' . $extension . ' is not loaded');
        }
    }

    if (gethostbyname(RIG_HOST) === RIG_HOST) {
        die('skip the test rig is not running, see tests/rig');
    }
}

//
// "encrypted" or "plaintext", from the session's own status
//
function rig_state($_link): string
{
    $sql = "show session status like 'Ssl_cipher'";

    if ($_link instanceof PDO) {
        $row = $_link->query($sql)->fetch(PDO::FETCH_NUM);
    } else {
        $row = $_link->query($sql)->fetch_row();
    }

    return ($row[1] === '') ? 'plaintext' : 'encrypted';
}

//
// connects with mysqli and returns "encrypted", "plaintext" or
// "error <errno>". $_init can set options on the handle before it connects.
//
function rig_mysqli(?string $_host, int $_flags = 0, ?callable $_init = null,
    string $_user = RIG_USER, string $_pass = RIG_PASS, ?string $_socket = null): string
{
    mysqli_report(MYSQLI_REPORT_OFF);

    $link = mysqli_init();

    if (is_null($_init) === false) {
        $_init($link);
    }

    if (@$link->real_connect($_host, $_user, $_pass, RIG_DB, RIG_PORT, $_socket, $_flags) === false) {
        return 'error ' . mysqli_connect_errno();
    }

    $state = rig_state($link);

    $link->close();

    return $state;
}

//
// connects with PDO and returns "encrypted", "plaintext" or "error <errno>".
// $_target is the host, or a whole DSN body when it carries an "=".
//
function rig_pdo(string $_target, array $_options = [], string $_user = RIG_USER,
    string $_pass = RIG_PASS): string
{
    if (strpos($_target, '=') === false) {
        $_target = 'host=' . $_target . ';port=' . RIG_PORT;
    }

    try {
        $pdo = @new PDO('mysql:' . $_target . ';dbname=' . RIG_DB, $_user, $_pass, $_options);
    } catch (PDOException $e) {
        return 'error ' . $e->getCode();
    }

    return rig_state($pdo);
}

//
// PDO's MySQL attributes moved from PDO::MYSQL_ATTR_* to Pdo\Mysql::ATTR_* in
// PHP 8.4; use whichever this PHP has without tripping a deprecation.
//
function rig_pdo_attr(string $_name): int
{
    if (class_exists('Pdo\\Mysql') === true) {
        return constant('Pdo\\Mysql::ATTR_' . $_name);
    }

    return constant('PDO::MYSQL_ATTR_' . $_name);
}
