<?php
header('Content-Type: application/json');
require_once '../includes/require_admin_api.php';
require_once '../includes/db.php';

$conn->query(
    "ALTER TABLE users
        ADD COLUMN IF NOT EXISTS full_name VARCHAR(120) NULL AFTER username,
        ADD COLUMN IF NOT EXISTS email VARCHAR(190) NULL AFTER full_name,
        ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL AFTER email,
        ADD COLUMN IF NOT EXISTS role VARCHAR(30) NOT NULL DEFAULT 'User' AFTER phone"
);

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid ID']);
    exit;
}

if (!empty($_SESSION['user_id']) && (int) $_SESSION['user_id'] === $id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'You cannot delete the currently signed in account.']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
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
