#!/bin/sh
#
# Copyright (c) 2026 Mike Pultz. Licensed under BSD-3-Clause.
#
# Drives the test rig.
#
#   ./rig.sh run [image] [binary]   build the extension in that PHP image and
#                                   run the suite; starts the MySQL servers
#                                   when they are not up yet
#   ./rig.sh down                   stop the servers, remove the certificates
#
# The image is an official PHP image (default php:8.5-cli), or
# "remi:<version>" for AlmaLinux 9 with that PHP version from Remi's
# repository. With a binary (a mysqlnd_tls.so built for that PHP), the suite
# runs against it and nothing is built.
#
set -e

cd "$(dirname "$0")"

case "$1" in
    run)
        PHP_IMAGE="${2:-php:8.5-cli}"
        RIG_TAG="$(printf '%s' "$PHP_IMAGE" | tr ':/' '--')"
        RIG_DOCKERFILE=Dockerfile
        RIG_PHP_VERSION=
        RIG_PREBUILT=/dev/null

        case "$PHP_IMAGE" in
            remi:*)
                RIG_DOCKERFILE=Dockerfile.remi
                RIG_PHP_VERSION="${PHP_IMAGE#remi:}"
                ;;
        esac

        if [ -n "$3" ]; then
            RIG_PREBUILT="$(cd "$OLDPWD" && cd "$(dirname "$3")" && pwd)/$(basename "$3")"
        fi

        export PHP_IMAGE RIG_TAG RIG_DOCKERFILE RIG_PHP_VERSION RIG_PREBUILT

        if [ ! -d certs ]; then
            ./gen-certs.sh
        fi

        docker compose --profile run build --quiet php
        docker compose --profile run run --rm php
        ;;
    down)
        docker compose --profile run down --volumes
        rm -rf certs
        ;;
    *)
        echo "usage: $0 run [image] [binary] | down" >&2
        exit 2
        ;;
esac
