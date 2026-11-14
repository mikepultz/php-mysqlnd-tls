#!/bin/sh
#
# Copyright (c) 2026 Mike Pultz. Licensed under BSD-3-Clause.
#
# Drives the test rig.
#
#   ./rig.sh run [php-image]   build the extension in that PHP image (default
#                              php:8.5-cli) and run the suite; starts the
#                              MySQL servers when they are not up yet
#   ./rig.sh down              stop the servers and remove the certificates
#
set -e

cd "$(dirname "$0")"

case "$1" in
    run)
        PHP_IMAGE="${2:-php:8.5-cli}"
        RIG_TAG="$(printf '%s' "$PHP_IMAGE" | tr ':/' '--')"
        export PHP_IMAGE RIG_TAG

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
        echo "usage: $0 run [php-image] | down" >&2
        exit 2
        ;;
esac
