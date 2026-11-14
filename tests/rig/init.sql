--
-- Copyright (c) 2026 Mike Pultz. Licensed under BSD-3-Clause.
--
-- accounts for the test rig: "tls" connects with or without TLS, "x509"
-- only with a client certificate issued by the test CA.
--
create user 'tls'@'%' identified by 'tlspass';

grant all on test.* to 'tls'@'%';

create user 'x509'@'%' identified by 'x509pass' require x509;

grant all on test.* to 'x509'@'%';
