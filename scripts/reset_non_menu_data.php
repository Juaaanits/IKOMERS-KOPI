<?php
require_once __DIR__ . '/../includes/db.php';

if (!$conn || !($conn instanceof mysqli) || $conn->connect_errno !== 0) {
    fwrite(STDERR, "Database connection failed.\n");
    exit(1);
}

$tablesToClear = [
    'orders',
    'customers',
    'system_users'
];

foreach ($tablesToClear as $table) {
    $existsResult = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
    if (!$existsResult || $existsResult->num_rows === 0) {
        if ($existsResult) {
            $existsResult->free();
        }
        continue;
    }
    $existsResult->free();

    if (!$conn->query("DELETE FROM `$table`")) {
        fwrite(STDERR, "Failed clearing table: $table\n");
        exit(1);
    }

    if (!$conn->query("ALTER TABLE `$table` AUTO_INCREMENT = 1")) {
        fwrite(STDERR, "Failed resetting AUTO_INCREMENT for: $table\n");
        exit(1);
    }
}

fwrite(STDOUT, "Reset complete. Preserved table data: menu_items\n");

$conn->close();
