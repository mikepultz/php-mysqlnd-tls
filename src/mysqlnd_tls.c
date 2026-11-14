/*
 * Copyright (c) 2026 Mike Pultz. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted under the terms of the BSD-3-Clause license.
 * See the LICENSE file for details.
 *
 * php-mysqlnd-tls: settings, the mysqlnd connect hook and the module entry.
 */

#include "php_mysqlnd_tls.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "ext/mysqlnd/mysqlnd.h"
#include "ext/mysqlnd/mysqlnd_ext_plugin.h"

ZEND_DECLARE_MODULE_GLOBALS(mysqlnd_tls)

/* mysqlnd's own connect, saved when the hook is installed */
static func_mysqlnd_conn_data__connect mysqlnd_tls_orig_connect = NULL;

/*
 * every setting is PHP_INI_SYSTEM: php.ini, or php_admin_value and
 * php_admin_flag in the web server configuration. Application code
 * (ini_set(), .htaccess, .user.ini) cannot change the policy.
 */
PHP_INI_BEGIN()
	STD_PHP_INI_BOOLEAN("mysqlnd_tls.enabled", "0", PHP_INI_SYSTEM, OnUpdateBool,
		enabled, zend_mysqlnd_tls_globals, mysqlnd_tls_globals)
	STD_PHP_INI_ENTRY("mysqlnd_tls.hosts", "", PHP_INI_SYSTEM, OnUpdateString,
		hosts, zend_mysqlnd_tls_globals, mysqlnd_tls_globals)
	STD_PHP_INI_BOOLEAN("mysqlnd_tls.verify", "1", PHP_INI_SYSTEM, OnUpdateBool,
		verify, zend_mysqlnd_tls_globals, mysqlnd_tls_globals)
	STD_PHP_INI_ENTRY("mysqlnd_tls.ca", "", PHP_INI_SYSTEM, OnUpdateString,
		ca, zend_mysqlnd_tls_globals, mysqlnd_tls_globals)
	STD_PHP_INI_ENTRY("mysqlnd_tls.capath", "", PHP_INI_SYSTEM, OnUpdateString,
		capath, zend_mysqlnd_tls_globals, mysqlnd_tls_globals)
PHP_INI_END()

/*
 * true when the host a connection is being opened to is covered by
 * mysqlnd_tls.hosts: a comma separated list, compared without regard to
 * case, where "*" covers every host reached over TCP.
 */
static bool mysqlnd_tls_host_listed(const char *host, size_t host_len)
{
	const char *entry = MYSQLND_TLS_G(hosts);

	if (host == NULL || entry == NULL) {
		return false;
	}

	/* a fully qualified name may carry a trailing dot */
	while (host_len > 0 && host[host_len - 1] == '.') {
		host_len--;
	}

	/*
	 * mysqlnd turns an empty host into "localhost", and "localhost" into
	 * a unix socket: there is no TCP connection to encrypt.
	 */
	if (host_len == 0 || zend_binary_strcasecmp(host, host_len,
			"localhost", sizeof("localhost") - 1) == 0) {
		return false;
	}

	while (*entry != '\0') {
		const char *end;
		size_t len;

		while (*entry == ',' || *entry == ' ' || *entry == '\t') {
			entry++;
		}

		end = entry;
		while (*end != '\0' && *end != ',') {
			end++;
		}

		len = (size_t) (end - entry);
		while (len > 0 && (entry[len - 1] == ' ' || entry[len - 1] == '\t')) {
			len--;
		}

		if (len == 1 && *entry == '*') {
			return true;
		}

		if (zend_binary_strcasecmp(entry, len, host, host_len) == 0) {
			return true;
		}

		entry = end;
	}

	return false;
}

/*
 * the connect hook. mysqli and pdo_mysql both open their connections
 * through this method, so the policy reaches every application without
 * the application knowing.
 *
 * The policy is applied through the connect flags and the documented
 * client options only, never through mysqlnd's own structures: at the TLS
 * switch mysqlnd takes the verify mode from the flags, ahead of any
 * option the application set on the connection.
 */
static enum_func_status
MYSQLND_METHOD(mysqlnd_tls, connect)(MYSQLND_CONN_DATA *conn,
	MYSQLND_CSTRING hostname, MYSQLND_CSTRING username,
	MYSQLND_CSTRING password, MYSQLND_CSTRING database,
	unsigned int port, MYSQLND_CSTRING socket_or_pipe,
	unsigned int mysql_flags)
{
	if (MYSQLND_TLS_G(enabled) &&
			mysqlnd_tls_host_listed(hostname.s, hostname.l)) {
		/*
		 * with this flag mysqlnd refuses the connection when the
		 * server does not offer TLS; there is no plaintext fallback.
		 */
		mysql_flags |= CLIENT_SSL;

		if (MYSQLND_TLS_G(verify)) {
			const char *ca     = MYSQLND_TLS_G(ca);
			const char *capath = MYSQLND_TLS_G(capath);

			/*
			 * our trust store replaces whatever the application
			 * set. With neither setting, PHP falls back to
			 * openssl.cafile and openssl.capath, then to the
			 * system store.
			 */
			conn->m->set_client_option(conn, MYSQLND_OPT_SSL_CA,
				(ca != NULL && *ca != '\0') ? ca : NULL);
			conn->m->set_client_option(conn, MYSQLND_OPT_SSL_CAPATH,
				(capath != NULL && *capath != '\0') ? capath : NULL);

			mysql_flags |= CLIENT_SSL_VERIFY_SERVER_CERT;
			mysql_flags &= ~CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
		} else {
			/*
			 * verify off applies just as firmly: an application
			 * that asks for verification itself is not verified
			 * either, so the setting means the same for every
			 * application.
			 */
			mysql_flags |= CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
			mysql_flags &= ~CLIENT_SSL_VERIFY_SERVER_CERT;
		}
	}

	return mysqlnd_tls_orig_connect(conn, hostname, username, password,
		database, port, socket_or_pipe, mysql_flags);
}

static PHP_GINIT_FUNCTION(mysqlnd_tls)
{
#if defined(COMPILE_DL_MYSQLND_TLS) && defined(ZTS)
	ZEND_TSRMLS_CACHE_UPDATE();
#endif
	memset(mysqlnd_tls_globals, 0, sizeof(*mysqlnd_tls_globals));
}

PHP_MINIT_FUNCTION(mysqlnd_tls)
{
	MYSQLND_CLASS_METHODS_TYPE(mysqlnd_conn_data) *methods;

	REGISTER_INI_ENTRIES();

	/*
	 * mysqlnd resets its method table when it starts, so the hook has to
	 * go in after that; the module dependency below orders the startup.
	 */
	methods = mysqlnd_conn_data_get_methods();

	mysqlnd_tls_orig_connect = methods->connect;
	methods->connect         = MYSQLND_METHOD(mysqlnd_tls, connect);

	return SUCCESS;
}

PHP_MSHUTDOWN_FUNCTION(mysqlnd_tls)
{
	MYSQLND_CLASS_METHODS_TYPE(mysqlnd_conn_data) *methods =
		mysqlnd_conn_data_get_methods();

	/* leave no pointer into this module behind in mysqlnd's table */
	if (methods->connect == MYSQLND_METHOD(mysqlnd_tls, connect)) {
		methods->connect = mysqlnd_tls_orig_connect;
	}

	UNREGISTER_INI_ENTRIES();

	return SUCCESS;
}

PHP_MINFO_FUNCTION(mysqlnd_tls)
{
	php_info_print_table_start();
	php_info_print_table_row(2, "mysqlnd_tls support", "enabled");
	php_info_print_table_row(2, "Extension version", PHP_MYSQLND_TLS_VERSION);
	php_info_print_table_end();

	DISPLAY_INI_ENTRIES();
}

/*
 * mysqlnd carries the connections, and its TLS runs through the stream
 * encryption of the openssl extension: without that every connection the
 * policy covers would fail, so PHP refuses to start the module instead.
 */
static const zend_module_dep mysqlnd_tls_deps[] = {
	ZEND_MOD_REQUIRED("mysqlnd")
	ZEND_MOD_REQUIRED("openssl")
	ZEND_MOD_END
};

zend_module_entry mysqlnd_tls_module_entry = {
	STANDARD_MODULE_HEADER_EX,
	NULL,
	mysqlnd_tls_deps,
	"mysqlnd_tls",
	NULL,                           /* functions */
	PHP_MINIT(mysqlnd_tls),
	PHP_MSHUTDOWN(mysqlnd_tls),
	NULL,                           /* RINIT */
	NULL,                           /* RSHUTDOWN */
	PHP_MINFO(mysqlnd_tls),
	PHP_MYSQLND_TLS_VERSION,
	PHP_MODULE_GLOBALS(mysqlnd_tls),
	PHP_GINIT(mysqlnd_tls),
	NULL,                           /* GSHUTDOWN */
	NULL,                           /* post deactivate */
	STANDARD_MODULE_PROPERTIES_EX
};

#ifdef COMPILE_DL_MYSQLND_TLS
# ifdef ZTS
ZEND_TSRMLS_CACHE_DEFINE()
# endif
ZEND_GET_MODULE(mysqlnd_tls)
#endif
