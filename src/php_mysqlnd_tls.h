/*
 * Copyright (c) 2026 Mike Pultz. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted under the terms of the BSD-3-Clause license.
 * See the LICENSE file for details.
 *
 * php-mysqlnd-tls: turns on TLS for mysqlnd connections from php.ini.
 */

#ifndef PHP_MYSQLND_TLS_H
#define PHP_MYSQLND_TLS_H

#ifdef HAVE_CONFIG_H
# include "config.h"
#endif

#include <php.h>

#include <stdbool.h>

/*
 * 7.4 is the oldest release whose mysqlnd speaks caching_sha2_password,
 * the default authentication of every MySQL server still supported.
 */
#if PHP_VERSION_ID < 70400
# error "php-mysqlnd-tls requires PHP >= 7.4"
#endif

#define PHP_MYSQLND_TLS_VERSION "1.0.0"

extern zend_module_entry mysqlnd_tls_module_entry;
#define phpext_mysqlnd_tls_ptr &mysqlnd_tls_module_entry

/*
 * OnUpdateBool stores a zend_bool (an unsigned char) before PHP 8.0 and a
 * bool from 8.0 on; zend_bool itself is gone in 8.4.
 */
#if PHP_VERSION_ID < 80000
typedef zend_bool mysqlnd_tls_bool;
#else
typedef bool mysqlnd_tls_bool;
#endif

ZEND_BEGIN_MODULE_GLOBALS(mysqlnd_tls)
	mysqlnd_tls_bool enabled;
	mysqlnd_tls_bool verify;
	char *hosts;
	char *ca;
	char *capath;
ZEND_END_MODULE_GLOBALS(mysqlnd_tls)

ZEND_EXTERN_MODULE_GLOBALS(mysqlnd_tls)

#define MYSQLND_TLS_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(mysqlnd_tls, v)

#if defined(ZTS) && defined(COMPILE_DL_MYSQLND_TLS)
ZEND_TSRMLS_CACHE_EXTERN()
#endif

#endif /* PHP_MYSQLND_TLS_H */
