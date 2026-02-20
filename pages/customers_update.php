<?php
header('Content-Type: application/json');
require_once '../includes/require_admin_api.php';
require_once '../includes/db.php';

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

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$ordersCount = isset($_POST['orders_count']) ? (int) $_POST['orders_count'] : -1;

if ($id <= 0 || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $ordersCount < 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid input']);
    exit;
}

$stmt = $conn->prepare('UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, orders_count = ? WHERE id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to prepare update query']);
    exit;
}

$stmt->bind_param('ssssii', $name, $email, $phone, $address, $ordersCount, $id);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to update customer']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'Customer updated successfully']);

