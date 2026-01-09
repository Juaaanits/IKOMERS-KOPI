<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));

// Temporary static data to illustrate the menu grid
$menuItems = [
    [
        'name' => 'Black Coffee',
        'price' => 4.25,
        'description' => 'Bold brewed coffee with a smooth finish.',
        'image' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'name' => 'Cappuccino',
        'price' => 4.75,
        'description' => 'Equal parts espresso, steamed milk, and foam.',
        'image' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=600&q=80&sat=-40'
    ],
    [
        'name' => 'Latte',
        'price' => 4.50,
        'description' => 'Creamy steamed milk over a rich espresso shot.',
        'image' => 'https://images.unsplash.com/photo-1510626176961-4b37d0b4e904?auto=format&fit=crop&w=600&q=80'
    ],
    [
        'name' => 'Mocha',
        'price' => 5.10,
        'description' => 'Espresso, chocolate, and steamed milk harmony.',
        'image' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=600&q=80'
    ]
];
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
                    <article class="menu-card">
                        <div class="menu-card__image" style="background-image: url('<?php echo htmlspecialchars($item['image']); ?>');" role="img" aria-label="<?php echo htmlspecialchars($item['name']); ?>"></div>
                        <div class="menu-card__body">
                            <div class="menu-card__meta">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <span class="price-chip">$<?php echo number_format($item['price'], 2); ?></span>
                            </div>
                            <p class="menu-card__description"><?php echo htmlspecialchars($item['description']); ?></p>
                            <div class="menu-card__actions">
                                <button type="button" class="icon-btn icon-btn--edit" aria-label="Edit item">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 20H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M15.5 4.5L19.5 8.5L10 18H6V14L15.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button type="button" class="icon-btn icon-btn--delete" aria-label="Delete item">
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
                            <h3>Add New Menu Item</h3>
                        </div>
                        <button id="close-menu-modal" type="button" class="close-btn" aria-label="Close add menu form">×</button>
                    </header>

                    <div class="form-grid">
                        <label class="field">
                            <span>Name</span>
                            <input type="text" name="name" placeholder="Name" required>
                        </label>
                        <label class="field">
                            <span>Price</span>
                            <input type="number" step="0.01" name="price" placeholder="Price" required>
                        </label>
                        <label class="field field--full">
                            <span>Description</span>
                            <textarea name="description" rows="3" placeholder="Description"></textarea>
                        </label>
                        <label class="field field--full">
                            <span>Image</span>
                            <div class="dropzone" id="dropzone">
                                <input type="file" name="image" id="image-input" accept="image/*">
                                <p>Drag and drop an image here</p>
                                <a href="#" id="browse-files" aria-label="Browse files">Browse Files</a>
                            </div>
                        </label>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn--ghost" id="cancel-menu-item">Cancel</button>
                        <button type="submit" class="btn btn--primary">Add Item</button>
                    </div>
                </form>
            </dialog>
        </section>
    </main>
</div>
<script src="../assets/js/custom/menu.js"></script>
</body>
</html>
