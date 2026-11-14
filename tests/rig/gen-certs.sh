#!/bin/sh
#
# Copyright (c) 2026 Mike Pultz. Licensed under BSD-3-Clause.
#
# Generates the throwaway certificates the test rig uses, into ./certs:
#
#   ca.pem           the CA the tests trust
#   capath/          the same CA as an OpenSSL hashed directory
#   server.pem/.key  the MySQL server certificate, issued for db.test only
#   client.pem/.key  a client certificate, for the account that requires one
#   other-ca.pem     an unrelated CA, for the "not trusted" tests
#
set -e

dir="$(cd "$(dirname "$0")" && pwd)/certs"

rm -rf "$dir"
mkdir -p "$dir/capath"
cd "$dir"

openssl req -x509 -newkey rsa:2048 -nodes -days 3650 \
    -subj "/CN=mysqlnd_tls test CA" \
    -addext "basicConstraints=critical,CA:TRUE" \
    -keyout ca.key -out ca.pem 2>/dev/null

openssl req -x509 -newkey rsa:2048 -nodes -days 3650 \
    -subj "/CN=mysqlnd_tls other CA" \
    -addext "basicConstraints=critical,CA:TRUE" \
    -keyout other-ca.key -out other-ca.pem 2>/dev/null

openssl req -newkey rsa:2048 -nodes \
    -subj "/CN=db.test" \
    -keyout server.key -out server.csr 2>/dev/null

printf 'subjectAltName=DNS:db.test\n' > server.ext

openssl x509 -req -in server.csr -CA ca.pem -CAkey ca.key -CAcreateserial \
    -days 3650 -extfile server.ext -out server.pem 2>/dev/null

openssl req -newkey rsa:2048 -nodes \
    -subj "/CN=mysqlnd_tls test client" \
    -keyout client.key -out client.csr 2>/dev/null

openssl x509 -req -in client.csr -CA ca.pem -CAkey ca.key -CAcreateserial \
    -days 3650 -out client.pem 2>/dev/null

cp ca.pem "capath/$(openssl x509 -hash -noout -in ca.pem).0"

# the MySQL and PHP containers read these as other users
chmod 644 ./*.key
