<?php
require_once '../includes/require_admin.php';
require_once '../includes/db.php';

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));

$customers = [];
$dbReady = $conn && $conn instanceof mysqli && $conn->connect_errno === 0;

if ($dbReady) {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS customers (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            address VARCHAR(255) DEFAULT '',
            orders_count INT UNSIGNED NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    );

    $result = $conn->query("SELECT id, name, email, phone, address, orders_count FROM customers ORDER BY id DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        $result->free();
    }
}
