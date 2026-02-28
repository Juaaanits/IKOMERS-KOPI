CREATE DATABASE IF NOT EXISTS ikomers_db;
USE ikomers_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  full_name VARCHAR(120) NULL,
  email VARCHAR(190) NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(40) NULL,
  role VARCHAR(30) NOT NULL DEFAULT 'Admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  description TEXT NULL,
  image_path VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users (email);

INSERT INTO users (username, full_name, email, password, role)
VALUES ('admin', 'Admin', 'admin@brewbean.local', '$2y$12$iKHfElzBn9l1D5WH8RqmQ.xJL2X7PvgZoGWjY8PsdaJq5ChS56RN.', 'Admin')
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  email = VALUES(email),
  role = VALUES(role);
