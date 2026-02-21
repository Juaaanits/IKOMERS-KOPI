<?php
header('Content-Type: application/json');
require_once '../includes/require_admin_api.php';
require_once '../includes/db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? 'Uncategorized');
$price = trim($_POST['price'] ?? '');
$description = trim($_POST['description'] ?? '');
$allowedCategories = ['Espresso', 'Cold Brew', 'Tea', 'Non-Coffee', 'Pastry', 'Uncategorized'];
if (!in_array($category, $allowedCategories, true)) {
    $category = 'Uncategorized';
}

if ($id <= 0 || $name === '' || !is_numeric($price) || (float)$price <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid input']);
    exit;
}

$categoryColumnResult = $conn->query("SHOW COLUMNS FROM menu_items LIKE 'category'");
$hasCategoryColumn = $categoryColumnResult && $categoryColumnResult->num_rows > 0;
if ($categoryColumnResult) {
    $categoryColumnResult->free();
}
if (!$hasCategoryColumn) {
    $conn->query("ALTER TABLE menu_items ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'Uncategorized' AFTER name");
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

if (isset($_FILES['image']) && is_array($_FILES['image'])) {
    $uploadError = (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError !== UPLOAD_ERR_OK && $uploadError !== UPLOAD_ERR_NO_FILE) {
        $message = 'Image upload failed.';
        if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            $message = 'Image is too large for server upload limit.';
        } elseif ($uploadError === UPLOAD_ERR_PARTIAL) {
            $message = 'Image upload was interrupted. Please try again.';
        } else {
            $message = 'Image upload failed (code ' . $uploadError . ').';
        }

        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => $message]);
        exit;
    }

    if ($uploadError === UPLOAD_ERR_OK) {
        $tmpFile = $_FILES['image']['tmp_name'];
        $originalName = $_FILES['image']['name'] ?? '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $imageInfo = @getimagesize($tmpFile);
        $mimeType = is_array($imageInfo) ? ($imageInfo['mime'] ?? '') : '';
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        ];

        if ($extension === '' && isset($mimeToExt[$mimeType])) {
            $extension = $mimeToExt[$mimeType];
        }

        if ($imageInfo === false || !in_array($extension, $allowedExt, true) || !in_array($mimeType, $allowedMime, true)) {
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
}

$priceVal = (float)$price;
$upd = $conn->prepare('UPDATE menu_items SET name=?, category=?, price=?, description=?, image_path=? WHERE id=?');
$upd->bind_param('ssdssi', $name, $category, $priceVal, $description, $newImagePath, $id);
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
        'category' => $category,
        'price' => number_format($priceVal, 2, '.', ''),
        'description' => $description,
        'image' => $newImagePath
    ]
]);

