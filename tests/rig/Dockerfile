# Copyright (c) 2026 Mike Pultz. Licensed under BSD-3-Clause.
#
# A stock PHP image plus the two MySQL extensions the tests drive. mysqlnd
# and openssl are already compiled into the official images.

ARG PHP_IMAGE=php:8.5-cli
FROM ${PHP_IMAGE}

RUN docker-php-ext-install mysqli pdo_mysql
