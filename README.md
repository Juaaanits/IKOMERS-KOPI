# IKOMERS KOPI

Coffee shop web app built with PHP and MariaDB/MySQL, intended to run on XAMPP. Docker assets were removed; these instructions focus on a local XAMPP setup.

## Requirements
- XAMPP with Apache and MySQL/MariaDB (tested on PHP 8+ / MariaDB 10+)
- A MySQL user with rights on a local database

## Quick start on XAMPP
1. Copy the project to `C:\xampp\htdocs\IKOMERS-KOPI` (or clone there).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Create the database (via phpMyAdmin or mysql CLI):
   ```sql
   CREATE DATABASE ikomers_db;
   USE ikomers_db;
   CREATE TABLE users (
     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
     username VARCHAR(50) UNIQUE NOT NULL,
     password VARCHAR(255) NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   -- Optional: seed a test user (password: password123)
   INSERT INTO users (username, password)
   VALUES ('demo', '$2y$10$H4LRuYh8zV6HnSgG0mOwzOdSHbWKV3GRj81K.T3fAukL1NhvFJ9tm');
   ```
4. Point the app at your DB in `includes/db.php`:
   ```php
   $host = "127.0.0.1"; // or localhost
   $user = "root";      // or your MySQL user
   $pass = "";          // your MySQL password
   $dbname = "ikomers_db";
   ```
5. Visit the site:
   - Home: `http://localhost/IKOMERS-KOPI/` (redirects to `pages/home.php`)
   - Login: `http://localhost/IKOMERS-KOPI/pages/login.php`
   - Sign up: `http://localhost/IKOMERS-KOPI/pages/signup.php`
   - Dashboard/Orders: requires a logged-in session

## Project structure
```
IKOMERS-KOPI/
├─ assets/           # CSS, JS, images
├─ includes/
│  └─ db.php         # DB connection settings
├─ pages/
│  ├─ home.php       # Landing page
│  ├─ login.php      # User login (checks users table)
│  ├─ signup.php     # Creates users with hashed passwords
│  ├─ dashboard.php  # Protected; requires session
│  └─ orders.php     # Protected; requires session
└─ index.php         # Redirects to pages/home.php
```

## Common issues
- **Cannot connect / host not allowed**: ensure MySQL user exists for `localhost` and `127.0.0.1` (`GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '...'; FLUSH PRIVILEGES;`).
- **phpMyAdmin login fails**: set host to `127.0.0.1` in `C:\xampp\phpMyAdmin\config.inc.php` and use the same credentials as `db.php`.
- **Styles not loading**: confirm the project folder name is `IKOMERS-KOPI` under `htdocs` or adjust asset paths if you moved it.
- **Sessions not persisting**: ensure PHP sessions are enabled in XAMPP and that the browser allows cookies for `localhost`.

## Quick start with Docker 
1. Install Docker Desktop (WSL2 backend on Windows).
2. Build and start the stack:
   ```bash
   docker compose up -d
   ```
   - App: http://localhost:8080
   - phpMyAdmin: http://localhost:8081 (user: `root`, pass: `rootpass`)
   - DB exposed on host port 3307 (mapped to container 3306) to avoid clashing with local MySQL.
3. App DB settings (also set via `docker-compose.yml` env vars and `includes/db.php` fallback):
   - Host: `db`
   - Database: `ikomers_db`
   - User: `appuser`
   - Password: `appsecret`
4. Schema/seed: auto-applied from `docker/init.sql` on first run. To re-seed, remove the `db_data` volume:
   ```bash
   docker compose down -v
   docker compose up -d
   ```

