<?php
header('Content-Type: application/json');
require_once '../includes/require_admin_api.php';
require_once '../includes/db.php';

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn->query(
    "ALTER TABLE users
        ADD COLUMN IF NOT EXISTS full_name VARCHAR(120) NULL AFTER username,
        ADD COLUMN IF NOT EXISTS email VARCHAR(190) NULL AFTER full_name,
        ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL AFTER email,
        ADD COLUMN IF NOT EXISTS role VARCHAR(30) NOT NULL DEFAULT 'Admin' AFTER phone"
);

$stmt = $conn->prepare(
    'SELECT id, username, full_name, email, phone, role
     FROM users
     WHERE id = ?
     LIMIT 1'
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to prepare query']);
    exit;
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$user) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'User not found']);
    exit;
}

$displayName = trim((string) ($user['full_name'] ?? ''));
if ($displayName === '') {
    $displayName = (string) ($user['username'] ?? 'Admin');
}

echo json_encode([
    'ok' => true,
    'profile' => [
        'id' => (int) $user['id'],
        'name' => $displayName,
        'email' => (string) ($user['email'] ?? ''),
        'phone' => (string) ($user['phone'] ?? ''),
        'role' => trim((string) ($user['role'] ?? '')) !== '' ? (string) $user['role'] : 'Admin'
    ]
]);

