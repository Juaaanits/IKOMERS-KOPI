<?php
header('Content-Type: application/json');
require_once '../includes/require_admin_api.php';
require_once '../includes/db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$price = trim($_POST['price'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($id <= 0 || $name === '' || !is_numeric($price) || (float)$price <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid input']);
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

$newImagePath = $item['image_path'];
$oldImagePath = $item['image_path'];

if (isset($_FILES['image']) && is_array($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmpFile = $_FILES['image']['tmp_name'];
    $originalName = $_FILES['image']['name'] ?? '';
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $mimeType = function_exists('mime_content_type') ? mime_content_type($tmpFile) : '';
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!in_array($extension, $allowedExt, true) || !in_array($mimeType, $allowedMime, true)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Invalid image file']);
        exit;
    }

    $menuDir = realpath(__DIR__ . '/../assets/uploads/menu');
    if ($menuDir === false) {
        $menuDir = __DIR__ . '/../assets/uploads/menu';
        if (!is_dir($menuDir)) {
            if (!mkdir($menuDir, 0775, true) && !is_dir($menuDir)) {
                http_response_code(500);
                echo json_encode(['ok' => false, 'message' => 'Failed to create upload directory']);
                exit;
            }
        }
    }

    $fileName = bin2hex(random_bytes(8)) . '.' . $extension;
    $target = $menuDir . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($tmpFile, $target)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Failed to save image']);
        exit;
    }

    $newImagePath = '../assets/uploads/menu/' . $fileName;
}

$priceVal = (float)$price;
$upd = $conn->prepare('UPDATE menu_items SET name=?, price=?, description=?, image_path=? WHERE id=?');
$upd->bind_param('sdssi', $name, $priceVal, $description, $newImagePath, $id);
$ok = $upd->execute();
$upd->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Update failed']);
    exit;
}

// delete old image if replaced
if ($newImagePath !== $oldImagePath && strpos($oldImagePath, '../assets/uploads/menu/') === 0) {
    $projectRoot = realpath(__DIR__ . '/..');
    $absoluteOld = $projectRoot . DIRECTORY_SEPARATOR .
        str_replace(['../', '/'], ['', DIRECTORY_SEPARATOR], $oldImagePath);
    if (is_file($absoluteOld)) {
        @unlink($absoluteOld);
    }
}

echo json_encode([
    'ok' => true,
    'message' => 'Item updated',
    'item' => [
        'id' => $id,
        'name' => $name,
        'price' => number_format($priceVal, 2, '.', ''),
        'description' => $description,
        'image' => $newImagePath
    ]
]);

