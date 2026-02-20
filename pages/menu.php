<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));
$menuItems = [];
$menuResult = '';
$menuResultType = '';
$nameValue = '';
$priceValue = '';
$descriptionValue = '';

$defaultMenuItems = [
    ['name' => 'Black Coffee', 'price' => 4.25, 'description' => 'Bold brewed coffee with a smooth finish.', 'image' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80'],
    ['name' => 'Cappuccino', 'price' => 4.75, 'description' => 'Equal parts espresso, steamed milk, and foam.', 'image' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80&sat=-40'],
    ['name' => 'Latte', 'price' => 4.50, 'description' => 'Creamy steamed milk over a rich espresso shot.', 'image' => 'https://images.unsplash.com/photo-1510626176961-4b37d0b4e904?auto=format&fit=crop&w=600&q=80'],
    ['name' => 'Mocha', 'price' => 5.10, 'description' => 'Espresso, chocolate, and steamed milk harmony.', 'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=600&q=80']
];

$dbReady = $conn && $conn instanceof mysqli && $conn->connect_errno === 0;

if ($dbReady) {
    $conn->query(
        "CREATE TABLE IF NOT EXISTS menu_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            description TEXT NULL,
            image_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    );

    // Seed starter menu rows once so cards have real IDs for edit/delete actions.
    $countResult = $conn->query('SELECT COUNT(*) AS total FROM menu_items');
    if ($countResult) {
        $countRow = $countResult->fetch_assoc();
        $countResult->free();
        $totalRows = isset($countRow['total']) ? (int) $countRow['total'] : 0;

        if ($totalRows === 0) {
            $seedStmt = $conn->prepare('INSERT INTO menu_items (name, price, description, image_path) VALUES (?, ?, ?, ?)');
            if ($seedStmt) {
                foreach ($defaultMenuItems as $seedItem) {
                    $seedName = $seedItem['name'];
                    $seedPrice = (float) $seedItem['price'];
                    $seedDescription = $seedItem['description'];
                    $seedImage = $seedItem['image'];
                    $seedStmt->bind_param('sdss', $seedName, $seedPrice, $seedDescription, $seedImage);
                    $seedStmt->execute();
                }
                $seedStmt->close();
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_menu_item'])) {
    $nameValue = trim($_POST['name'] ?? '');
    $priceValue = trim($_POST['price'] ?? '');
    $descriptionValue = trim($_POST['description'] ?? '');

    if (!$dbReady) {
        $menuResult = 'Database is unavailable. Please try again later.';
        $menuResultType = 'error';
    } elseif ($nameValue === '' || strlen($nameValue) > 120) {
        $menuResult = 'Name is required and should be at most 120 characters.';
        $menuResultType = 'error';
    } elseif (!is_numeric($priceValue) || (float) $priceValue <= 0) {
        $menuResult = 'Price must be a number greater than 0.';
        $menuResultType = 'error';
    } elseif (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
        $menuResult = 'Please choose an image to upload.';
        $menuResultType = 'error';
    } else {
        $image = $_FILES['image'];
        $maxFileBytes = 5 * 1024 * 1024;
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (($image['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $menuResult = 'Image upload failed. Please select a valid image file.';
            $menuResultType = 'error';
        } elseif (($image['size'] ?? 0) > $maxFileBytes) {
            $menuResult = 'Image is too large. Maximum size is 5MB.';
            $menuResultType = 'error';
        } else {
            $tmpFile = $image['tmp_name'];
            $originalName = $image['name'] ?? '';
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $mimeType = function_exists('mime_content_type') ? mime_content_type($tmpFile) : '';

            if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
                $menuResult = 'Only JPG, PNG, WEBP, or GIF files are allowed.';
                $menuResultType = 'error';
            } else {
                $uploadDir = realpath(__DIR__ . '/../assets/uploads');
                if ($uploadDir === false) {
                    $uploadDir = __DIR__ . '/../assets/uploads';
                    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
                        $menuResult = 'Failed to prepare upload directory.';
                        $menuResultType = 'error';
                    }
                }

                $menuUploadDir = $uploadDir . '/menu';
                if ($menuResultType === '' && !is_dir($menuUploadDir) && !mkdir($menuUploadDir, 0775, true)) {
                    $menuResult = 'Failed to create menu upload directory.';
                    $menuResultType = 'error';
                }

                if ($menuResultType === '') {
                    $fileName = bin2hex(random_bytes(8)) . '.' . $extension;
                    $targetPath = $menuUploadDir . '/' . $fileName;
                    $dbImagePath = '../assets/uploads/menu/' . $fileName;

                    if (!move_uploaded_file($tmpFile, $targetPath)) {
                        $menuResult = 'Failed to save uploaded image.';
                        $menuResultType = 'error';
                    } else {
                        $insert = $conn->prepare('INSERT INTO menu_items (name, price, description, image_path) VALUES (?, ?, ?, ?)');
                        if (!$insert) {
                            @unlink($targetPath);
                            $menuResult = 'Unable to save menu item right now.';
                            $menuResultType = 'error';
                        } else {
                            $priceAsDecimal = (float) $priceValue;
                            $insert->bind_param('sdss', $nameValue, $priceAsDecimal, $descriptionValue, $dbImagePath);

                            if ($insert->execute()) {
                                $menuResult = 'Menu item added successfully.';
                                $menuResultType = 'success';
                                $nameValue = '';
                                $priceValue = '';
                                $descriptionValue = '';
                            } else {
                                @unlink($targetPath);
                                $menuResult = 'Failed to save the new menu item.';
                                $menuResultType = 'error';
                            }
                            $insert->close();
                        }
                    }
                }
            }
        }
    }
}

if ($dbReady) {
    $result = $conn->query('SELECT id, name, price, description, image_path AS image FROM menu_items ORDER BY id DESC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
        }
        $result->free();
    }
}

if (empty($menuItems)) {
    $menuItems = $defaultMenuItems;
}

if ($conn && $conn instanceof mysqli) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | IKOMERS KOPI</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/menu.css">
</head>
<body>
<div class="dashboard-layout">
    <aside class="sidebar" aria-label="Main navigation">
        <div class="brand">
            <span class="brand-icon" aria-hidden="true">BB</span>
            <span>BrewBean</span>
        </div>
        <nav class="sidebar-nav" aria-label="Sidebar menu">
            <ul>
                <li>
                    <a href="dashboard.php">
                        <span class="nav-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 9.5L12 3L20 9.5V20H4V9.5Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 20V13H14V20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="menu.php" aria-current="page">
                        <span class="nav-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 5H20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M4 12H20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M4 19H20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Menu</span>
                    </a>
                </li>
                <li>
                    <a href="customers.php">
                        <span class="nav-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 21V19C20 16.7909 18.2091 15 16 15H8C5.79086 15 4 16.7909 4 19V21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>Customers</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php">
                        <span class="nav-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 7H21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M7 11H17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M5 15H19" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M9 19H15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Orders</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span class="nav-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 21V19C16 17.8954 15.1046 17 14 17H10C8.89543 17 8 17.8954 8 19V21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M20 8V4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M22 6H18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M4 8V4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M6 6H2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Users</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a class="logout-link" href="logout.php">Log out</a>
            <span>Signed in as <strong><?php echo htmlspecialchars($username); ?></strong></span>
        </div>
    </aside>

    <main class="main-area" role="main">
        <header class="main-header">
            <div>
                <h1>Menu</h1>
                <p>Cafe shop menu items</p>
            </div>
            <div class="account-pill">
                <span>Hello, <?php echo htmlspecialchars($username); ?></span>
                <span class="account-avatar"><?php echo htmlspecialchars($initial); ?></span>
            </div>
        </header>

        <section class="menu-page" aria-label="Menu items">
            <?php if ($menuResult !== ''): ?>
                <div class="menu-feedback menu-feedback--<?php echo $menuResultType === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($menuResult); ?>
                </div>
            <?php endif; ?>
            <div class="menu-page__header">
                <div>
                    <p class="eyebrow">Cafe Shop</p>
                    <h2>Cafe Shop Menu Items</h2>
                </div>
                <div class="menu-page__filters">
                    <input type="search" name="search" placeholder="Search item" aria-label="Search menu items">
                    <select name="category" aria-label="Filter by category">
                        <option value="all">All</option>
                        <option value="espresso">Espresso</option>
                        <option value="cold">Cold Brew</option>
                        <option value="tea">Tea</option>
                    </select>
                </div>
            </div>

            <div class="menu-grid">
                <?php foreach ($menuItems as $item): ?>
                    <?php $hasId = isset($item['id']) && (int) $item['id'] > 0; ?>
                    <article class="menu-card">
                        <div class="menu-card__image" style="background-image: url('<?php echo htmlspecialchars($item['image']); ?>');" role="img" aria-label="<?php echo htmlspecialchars($item['name']); ?>"></div>
                        <div class="menu-card__body">
                            <div class="menu-card__meta">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <span class="price-chip">$<?php echo number_format($item['price'], 2); ?></span>
                            </div>
                            <p class="menu-card__description"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="menu-card__actions">
                                <button
                                    type="button"
                                    class="icon-btn icon-btn--edit js-edit-item"
                                    aria-label="Edit item"
                                    data-id="<?php echo $hasId ? (int) $item['id'] : ''; ?>"
                                    data-name="<?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?>"
                                    data-price="<?php echo htmlspecialchars(number_format((float)$item['price'], 2, '.', ''), ENT_QUOTES); ?>"
                                    data-description="<?php echo htmlspecialchars($item['description'] ?? '', ENT_QUOTES); ?>"
                                    data-image="<?php echo htmlspecialchars($item['image'] ?? '', ENT_QUOTES); ?>"
                                    <?php echo $hasId ? '' : 'disabled title="Demo item cannot be edited"'; ?>
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 20H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M15.5 4.5L19.5 8.5L10 18H6V14L15.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>

                                <button
                                    type="button"
                                    class="icon-btn icon-btn--delete js-delete-item"
                                    aria-label="Delete item"
                                    data-id="<?php echo $hasId ? (int) $item['id'] : ''; ?>"
                                    <?php echo $hasId ? '' : 'disabled title="Demo item cannot be deleted"'; ?>
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6 7H18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M10 11V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M14 11V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M5 7L6 19C6 20.1046 6.89543 21 8 21H16C17.1046 21 18 20.1046 18 19L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9 7V5C9 4.44772 9.44772 4 10 4H14C14.5523 4 15 4.44772 15 5V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <button id="menu-fab" class="menu-fab" type="button" aria-label="Add new menu item">+</button>

            <dialog id="menu-modal" class="menu-modal">
                <form id="menu-form" class="menu-modal__card" action="#" method="post" enctype="multipart/form-data" novalidate>
                    <header class="menu-modal__header">
                        <div>
                            <h3 id="menu-modal-title">Add New Menu Item</h3>
                        </div>
                        <button id="close-menu-modal" type="button" class="close-btn" aria-label="Close add menu form">&times;</button>
                    </header>

                    <input type="hidden" name="item_id" id="menu-item-id" value="">

                    <div class="form-grid">
                        <label class="field">
                            <span>Name</span>
                            <input type="text" name="name" placeholder="Name" value="<?php echo htmlspecialchars($nameValue, ENT_QUOTES); ?>" required>
                        </label>
                        <label class="field">
                            <span>Price</span>
                            <input type="number" step="0.01" min="0.01" name="price" placeholder="Price" value="<?php echo htmlspecialchars($priceValue, ENT_QUOTES); ?>" required>
                        </label>
                        <label class="field field--full">
                            <span>Description</span>
                            <textarea name="description" rows="3" placeholder="Description"><?php echo htmlspecialchars($descriptionValue); ?></textarea>
                        </label>
                        <label class="field field--full">
                            <span>Image</span>
                            <div class="dropzone" id="dropzone">
                                <input type="file" name="image" id="image-input" accept=".jpg,.jpeg,.png,.webp,.gif,image/*" required>
                                <p>Drag and drop an image here</p>
                                <a href="#" id="browse-files" aria-label="Browse files">Browse Files</a>
                                <p id="selected-image-name" class="dropzone__filename">No image selected yet</p>
                            </div>
                        </label>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn--ghost" id="cancel-menu-item">Cancel</button>
                        <button type="submit" class="btn btn--primary" name="add_menu_item" id="menu-submit-btn">Add Item</button>
                    </div>
                </form>
            </dialog>
        </section>
    </main>
</div>
<script src="../assets/js/custom/menu.js"></script>
<script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>

