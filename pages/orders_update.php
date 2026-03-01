<?php
header('Content-Type: application/json');
require_once '../includes/require_admin_api.php';
require_once '../includes/db.php';

$conn->query(
    "CREATE TABLE IF NOT EXISTS orders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(120) NOT NULL,
        items TEXT NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        status ENUM('Pending','Processing','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
        ordered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )"
);

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$status = trim($_POST['status'] ?? '');
$allowedStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];

if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid input']);
    exit;
}

$currentStatus = '';
$check = $conn->prepare('SELECT status FROM orders WHERE id = ? LIMIT 1');
if (!$check) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to prepare status check query']);
    exit;
}

$check->bind_param('i', $id);
$check->execute();
$checkResult = $check->get_result();
if ($checkResult && ($row = $checkResult->fetch_assoc())) {
    $currentStatus = (string) ($row['status'] ?? '');
}
if ($checkResult) {
    $checkResult->free();
}
$check->close();

if ($currentStatus === 'Completed') {
    http_response_code(409);
    echo json_encode(['ok' => false, 'message' => 'Completed orders are locked and cannot be edited.']);
    exit;
}

$stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to prepare update query']);
    exit;
}

$stmt->bind_param('si', $status, $id);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to update order']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'Order updated successfully']);
