#!/bin/sh
#
# Copyright (c) 2026 Mike Pultz. Licensed under BSD-3-Clause.
#
# Builds the pre-built binary that PIE installs, for one PHP version on this
# machine's architecture:
#
#   .github/scripts/build-binary.sh <php-version> <tag> <out-dir>
#
# It leaves mysqlnd_tls.so in <out-dir>, and next to it the archive under the
# name PIE looks for on the release:
#
#   php_mysqlnd_tls-<tag>_php<php-version>-<arch>-linux-glibc.zip
#
# The build runs in an AlmaLinux 9 container against Remi's PHP. EL9 has the
# oldest glibc still in common use, so the result loads on newer systems too,
# and Remi packages every PHP version. A module built against one
# distribution's PHP loads in another's, as long as the minor version and the
# thread safety match.
#
set -e

if [ "$1" != "--in-container" ]; then
    if [ "$#" -ne 3 ]; then
        echo "usage: $0 <php-version> <tag> <out-dir>" >&2
        exit 2
    fi

    root="$(cd "$(dirname "$0")/../.." && pwd)"

    mkdir -p "$3"
    out="$(cd "$3" && pwd)"

    exec docker run --rm -v "$root":/src:ro -v "$out":/out almalinux:9 \
        sh /src/.github/scripts/build-binary.sh --in-container "$1" "$2"
fi

version="$2"
tag="$3"
v="$(echo "$version" | tr -d .)"

dnf -y -q install https://rpms.remirepo.net/enterprise/remi-release-9.rpm
dnf -y -q install gcc make autoconf automake libtool zip \
    "php${v}-php-devel" "php${v}-php-cli" "php${v}-php-mysqlnd"

PATH="/opt/remi/php${v}/root/usr/bin:$PATH"

mkdir -p /build/src
cp /src/config.m4 /build/
cp /src/src/*.c /src/src/*.h /build/src/

cd /build

phpize
./configure

# hardened like a distribution package, and warnings are errors
make CFLAGS="-O2 -g -fstack-protector-strong -D_FORTIFY_SOURCE=2 -Wall -Wextra -Wno-unused-parameter -Werror" \
    LDFLAGS="-Wl,-z,relro -Wl,-z,now"

# it has to load in the PHP it was built for
php -d extension=/build/modules/mysqlnd_tls.so --ri mysqlnd_tls

case "$(uname -m)" in
    x86_64)
        arch=x86_64
        ;;
    aarch64)
        arch=arm64
        ;;
    *)
        echo "unsupported architecture: $(uname -m)" >&2
        exit 1
        ;;
esac

zts="$(php -r 'echo PHP_ZTS ? "-zts" : "";')"
name="$(echo "php_mysqlnd_tls-${tag}_php${version}-${arch}-linux-glibc${zts}.zip" | tr 'A-Z' 'a-z')"

cp modules/mysqlnd_tls.so /out/

cd /out
rm -f "$name"
zip -q "$name" mysqlnd_tls.so

echo "built $name"
