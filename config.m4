dnl Copyright (c) 2026 Mike Pultz. Licensed under BSD-3-Clause.
dnl
dnl config.m4 for the mysqlnd_tls extension.

PHP_ARG_ENABLE([mysqlnd_tls],
  [whether to enable mysqlnd_tls],
  [AS_HELP_STRING([--enable-mysqlnd-tls],
    [Turn on TLS for mysqlnd connections from php.ini])],
  [no])

if test "$PHP_MYSQLND_TLS" != "no"; then

  dnl
  dnl the extension hooks mysqlnd through its plugin API, so the mysqlnd
  dnl headers have to be installed; they ship with the PHP development
  dnl files whenever PHP itself was built with mysqlnd.
  dnl
  AC_MSG_CHECKING([for the mysqlnd headers])
  if test -f "$phpincludedir/ext/mysqlnd/mysqlnd_ext_plugin.h" ||
     test -f "$abs_srcdir/ext/mysqlnd/mysqlnd_ext_plugin.h"; then
    AC_MSG_RESULT([yes])
  else
    AC_MSG_RESULT([no])
    AC_MSG_ERROR([the mysqlnd headers were not found; PHP must be built with mysqlnd])
  fi

  PHP_NEW_EXTENSION([mysqlnd_tls],
    [src/mysqlnd_tls.c],
    [$ext_shared],,
    [-DZEND_ENABLE_STATIC_TSRMLS_CACHE=1])

  PHP_ADD_EXTENSION_DEP([mysqlnd_tls], [mysqlnd])
fi
