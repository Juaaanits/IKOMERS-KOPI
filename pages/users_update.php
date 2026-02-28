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
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role = trim($_POST['role'] ?? '');
$allowedRoles = ['Admin', 'User'];

if ($id <= 0 || $fullname === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, $allowedRoles, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid input']);
    exit;
}

$emailLower = strtolower($email);
if ($password !== '') {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE users SET username = ?, full_name = ?, email = ?, password = ?, phone = ?, role = ? WHERE id = ?');
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Failed to prepare update query']);
        exit;
    }
    $stmt->bind_param('ssssssi', $emailLower, $fullname, $emailLower, $passwordHash, $phone, $role, $id);
} else {
    $stmt = $conn->prepare('UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role = ? WHERE id = ?');
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Failed to prepare update query']);
        exit;
    }
    $stmt->bind_param('sssssi', $emailLower, $fullname, $emailLower, $phone, $role, $id);
}

$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to update user']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'User updated successfully']);
