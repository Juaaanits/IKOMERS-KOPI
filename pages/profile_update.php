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

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role = trim($_POST['role'] ?? 'Admin');
$allowedRoles = ['Admin', 'User'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'Admin';
}

if ($name === '' || strlen($name) > 120) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Name is required (max 120 chars).']);
    exit;
}

if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Enter a valid email address.']);
    exit;
}

if (strlen($phone) > 40) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Phone is too long (max 40 chars).']);
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
    'UPDATE users
     SET full_name = ?, email = ?, phone = ?, role = ?
     WHERE id = ?'
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to prepare update']);
    exit;
}

$stmt->bind_param('ssssi', $name, $email, $phone, $role, $userId);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to save profile']);
    exit;
}

echo json_encode([
    'ok' => true,
    'message' => 'Profile updated successfully.',
    'profile' => [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'role' => $role
    ]
]);
