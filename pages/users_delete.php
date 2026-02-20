<?php
header('Content-Type: application/json');
require_once '../includes/require_admin_api.php';
require_once '../includes/db.php';

$conn->query(
    "CREATE TABLE IF NOT EXISTS system_users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(40) DEFAULT '',
        role ENUM('Admin','User') NOT NULL DEFAULT 'User',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
);

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM system_users WHERE id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to prepare delete query']);
    exit;
}

$stmt->bind_param('i', $id);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to delete user']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'User deleted successfully']);
