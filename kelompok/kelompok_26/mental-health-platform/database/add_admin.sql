-- Run the following SQL after you generate a bcrypt/hash with PHP's password_hash
-- Example steps:
-- 1) In terminal run: php -r "echo password_hash('admin', PASSWORD_DEFAULT) . PHP_EOL;"
-- 2) Copy the resulting hash and paste into the INSERT statement below replacing <HASH>

INSERT INTO users (name,email,password,role,created_at)
VALUES ('Admin Astral','admin@astral.us','<HASH>','admin',NOW());
