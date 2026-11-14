#!/bin/sh
#
# Copyright (c) 2026 Mike Pultz. Licensed under BSD-3-Clause.
#
# Runs inside the rig's PHP container: builds the extension from the
# read-only /src mount in a scratch directory, with warnings as errors, and
# runs the .phpt suite against the rig's MySQL servers. When rig.sh was given
# a pre-built binary, that is tested and nothing is built.
#
set -e

rm -rf /build
mkdir -p /build/src /build/tests/rig

cp /src/config.m4 /build/
cp /src/src/*.c /src/src/*.h /build/src/
cp /src/tests/*.phpt /src/tests/*.inc /build/tests/
cp -r /src/tests/rig/certs /build/tests/rig/certs

cd /build

php -v | head -1

# also brings run-tests.php
phpize > /dev/null 2>&1

if [ -s /prebuilt/mysqlnd_tls.so ]; then
    echo "testing the pre-built binary"

    mkdir -p modules
    cp /prebuilt/mysqlnd_tls.so modules/
else
    ./configure > /dev/null

    if ! make CFLAGS="-g -O2 -Wall -Wextra -Wno-unused-parameter -Werror" > make.log 2>&1; then
        cat make.log
        exit 1
    fi
fi

#
# "make test" drops every extension= line of PHP's own ini files, so mysqli
# and pdo_mysql would be missing. Run the test runner directly and load what
# this PHP ships as shared modules, mysqlnd_tls last: it needs mysqlnd.
#
extdir="$(php-config --extension-dir)"
load=""

for name in openssl mysqlnd pdo mysqli pdo_mysql; do
    if [ -f "$extdir/$name.so" ]; then
        load="$load -d extension=$extdir/$name.so"
    fi
done

load="$load -d extension=/build/modules/mysqlnd_tls.so"

status=0

# shellcheck disable=SC2086
NO_COLOR=1 NO_INTERACTION=1 REPORT_EXIT_STATUS=1 TEST_PHP_EXECUTABLE="$(command -v php)" \
    php -n run-tests.php -n $load tests/ > test.log 2>&1 || status=1

tr '\r' '\n' < test.log | sed 's/\x1b\[[0-9;]*m//g' > results.log

grep -E '^(FAIL|SKIP|BORK|WARN|LEAK|Tests |Number of tests)' results.log || true

# with the rig up nothing has a reason to skip, so a skip is a failure too
if ! grep -q -E '^Tests skipped +: +0 ' results.log; then
    status=1
fi

if [ "$status" -ne 0 ]; then
    for f in tests/*.diff; do
        if [ -f "$f" ]; then
            echo "== $f"
            cat "$f"
        fi
    done
fi

exit "$status"
