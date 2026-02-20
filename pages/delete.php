<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare('SELECT image_path FROM menu_items WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$item = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$item) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Item not found']);
    exit;
}

$del = $conn->prepare('DELETE FROM menu_items WHERE id = ?');
$del->bind_param('i', $id);
$ok = $del->execute();
$del->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Delete failed']);
    exit;
}

// Delete local uploaded file
$imagePath = $item['image_path'] ?? '';
if (strpos($imagePath, '../assets/uploads/menu/') === 0) {
    $projectRoot = realpath(__DIR__ . '/..');
    $absolute = $projectRoot . DIRECTORY_SEPARATOR .
        str_replace(['../', '/'], ['', DIRECTORY_SEPARATOR], $imagePath);
    if (is_file($absolute)) {
        @unlink($absolute);
    }
}

echo json_encode(['ok' => true, 'message' => 'Item deleted']);

