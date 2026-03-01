USE ikomers_db;

-- Ensure required user-profile columns exist.
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS full_name VARCHAR(120) NULL AFTER username,
    ADD COLUMN IF NOT EXISTS email VARCHAR(190) NULL AFTER full_name,
    ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL AFTER email,
    ADD COLUMN IF NOT EXISTS role VARCHAR(30) NOT NULL DEFAULT 'User' AFTER phone;

-- Ensure unique email for username/email login flow.
CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users (email);

-- 5 demo accounts: 2 admins, 3 users.
-- Passwords are bcrypt-hashed and ready for password_verify().
INSERT INTO users (username, full_name, email, password, phone, role) VALUES
('admin@mail.com', 'Admin One', 'admin@mail.com', '$2y$10$qrMngp.7XDgdE04uh14lx.dkZH1sNSiPh3sv8eYYk6pNHmq5J2gme', '09170001001', 'Admin'),
('ops.admin@mail.com', 'Ops Admin', 'ops.admin@mail.com', '$2y$10$K8m583otq1Q.2W7a8Q2Q9eTcIppnbZKE3/a89y3eZ10zoC4oyjN1.', '09170001002', 'Admin'),
('juan.user@mail.com', 'Juan User', 'juan.user@mail.com', '$2y$10$YJjBTgAYxclxiMBhs9EQdOTWq.5hM7EppFRCrT5KZLgbu1x.cWo7C', '09170001003', 'User'),
('maria.user@mail.com', 'Maria User', 'maria.user@mail.com', '$2y$10$MFWuyPeOlC4.8RA9oFUWueYUqzpgqvp7WidckWuj.E9SVbwk2JkVi', '09170001004', 'User'),
('test.user@mail.com', 'Test User', 'test.user@mail.com', '$2y$10$G8tjq1OjF5xgGGF2EjaM..2OaHmEEOlbElxz43miMQ.enHFOxXkiS', '09170001005', 'User')
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    email = VALUES(email),
    password = VALUES(password),
    phone = VALUES(phone),
    role = VALUES(role);

