<?php
require_once '../includes/require_admin.php';
require_once '../includes/db.php';

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));

$customers = [];
$stats = [
    'totalCustomers' => 0,
    'revenuePerCustomer' => 0.0,
    'customersPerDay' => 0.0
];
$spendingDistribution = [
    '₱0-₱20' => 0,
    '₱20-₱50' => 0,
    '₱50-₱100' => 0,
    'No Spending' => 0,
    'Over ₱100' => 0
];
$perPage = 3;
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}
$totalPages = 1;

$dbReady = $conn && $conn instanceof mysqli && $conn->connect_errno === 0;

if ($dbReady) {
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty(trim($_POST['id'] ?? ''))) {
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $ordersCount = isset($_POST['orders_count']) ? (int) $_POST['orders_count'] : 0;

        if (
            $name !== '' &&
            strlen($name) <= 120 &&
            filter_var($email, FILTER_VALIDATE_EMAIL) &&
            $phone !== '' &&
            $ordersCount >= 0
        ) {
            $insert = $conn->prepare('INSERT INTO customers (name, email, phone, address, orders_count) VALUES (?, ?, ?, ?, ?)');
            if ($insert) {
                $insert->bind_param('ssssi', $name, $email, $phone, $address, $ordersCount);
                $insert->execute();
                $insert->close();
            }
        }

        header('Location: customers.php');
        exit;
    }

    $totalCustomers = 0;
    $countResult = $conn->query("SELECT COUNT(*) AS total FROM customers");
    if ($countResult) {
        $countRow = $countResult->fetch_assoc();
        $totalCustomers = (int) ($countRow['total'] ?? 0);
        $countResult->free();
    }

    $stats['totalCustomers'] = $totalCustomers;

    $ordersTableResult = $conn->query("SHOW TABLES LIKE 'orders'");
    $hasOrdersTable = $ordersTableResult && $ordersTableResult->num_rows > 0;
    if ($ordersTableResult) {
        $ordersTableResult->free();
    }

    if ($hasOrdersTable) {
        $revenueResult = $conn->query("SELECT COALESCE(SUM(total), 0) AS total_revenue FROM orders");
        $totalRevenue = 0.0;
        if ($revenueResult) {
            $revenueRow = $revenueResult->fetch_assoc();
            $totalRevenue = (float) ($revenueRow['total_revenue'] ?? 0);
            $revenueResult->free();
        }
        $stats['revenuePerCustomer'] = $totalCustomers > 0 ? ($totalRevenue / $totalCustomers) : 0.0;
    }

    $dateSpanResult = $conn->query("
        SELECT
            MIN(DATE(created_at)) AS first_day,
            MAX(DATE(created_at)) AS last_day
        FROM customers
    ");
    if ($dateSpanResult) {
        $dateSpanRow = $dateSpanResult->fetch_assoc();
        $dateSpanResult->free();
        if (!empty($dateSpanRow['first_day']) && !empty($dateSpanRow['last_day'])) {
            $first = new DateTime($dateSpanRow['first_day']);
            $last = new DateTime($dateSpanRow['last_day']);
            $days = max(1, (int) $first->diff($last)->days + 1);
            $stats['customersPerDay'] = $totalCustomers > 0 ? ($totalCustomers / $days) : 0.0;
        }
    }

    $spendingQuery = $conn->query("
        SELECT orders_count, COUNT(*) AS total
        FROM customers
        GROUP BY orders_count
    ");
    if ($spendingQuery) {
        while ($bucketRow = $spendingQuery->fetch_assoc()) {
            $ordersCount = (int) $bucketRow['orders_count'];
            $bucketTotal = (int) $bucketRow['total'];
            if ($ordersCount === 0) {
                $spendingDistribution['No Spending'] += $bucketTotal;
            } elseif ($ordersCount <= 5) {
                $spendingDistribution['₱0-₱20'] += $bucketTotal;
            } elseif ($ordersCount <= 12) {
                $spendingDistribution['₱20-₱50'] += $bucketTotal;
            } elseif ($ordersCount <= 20) {
                $spendingDistribution['₱50-₱100'] += $bucketTotal;
            } else {
                $spendingDistribution['Over ₱100'] += $bucketTotal;
            }
        }
        $spendingQuery->free();
    }

    $totalPages = max(1, (int) ceil($totalCustomers / $perPage));
    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }

    $offset = ($currentPage - 1) * $perPage;

    // check if orders table exists
    $ordersTableExists = false;
    $ordersTableCheck = $conn->query("SHOW TABLES LIKE 'orders'");
    if ($ordersTableCheck) {
        $ordersTableExists = $ordersTableCheck->num_rows > 0;
        $ordersTableCheck->free();
    }

    if ($ordersTableExists) {
        // dynamic orders count per customer (matched by customer name)
        $sql = "
            SELECT
                c.id,
                c.name,
                c.email,
                c.phone,
                c.address,
                COALESCE(o.orders_count, 0) AS orders_count
            FROM customers c
            LEFT JOIN (
                SELECT customer_name, COUNT(*) AS orders_count
                FROM orders
                GROUP BY customer_name
            ) o ON o.customer_name = c.name
            ORDER BY c.id DESC
            LIMIT ? OFFSET ?
        ";
    } else {
        // fallback if orders table not yet created
        $sql = "
            SELECT
                id, name, email, phone, address,
                0 AS orders_count
            FROM customers
            ORDER BY id DESC
            LIMIT ? OFFSET ?
        ";
}

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        $result->free();
    }
    $stmt->close();
}

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
    <title>Customers | IKOMERS KOPI</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/dashboard.css'); ?>">
    <link rel="stylesheet" href="../assets/css/customers.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/customers.css'); ?>">
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
                    <a href="menu.php">
                        <span class="nav-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M5 7.5C5 6.39543 5.89543 5.5 7 5.5H17C18.1046 5.5 19 6.39543 19 7.5V18.5H5V7.5Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 10H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <path d="M8 13H14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span>Menu</span>
                    </a>
                </li>
                <li>
                    <a href="customers.php" aria-current="page">
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
                    <a href="users.php">
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
            <a class="logout-link" href="logout.php">Sign Out</a>
            <span>Signed in as <strong><?php echo htmlspecialchars($username); ?></strong></span>
        </div>
    </aside>

    <main class="main-area" role="main">
        <header class="main-header">
            <div>
                <h1>Dashboard</h1>
            </div>
            <div class="account-menu">
                <button class="account-trigger" id="account-trigger" type="button" aria-expanded="false" aria-controls="account-dropdown">
                    <span>Hello, <?php echo htmlspecialchars($username); ?></span>
                    <span class="account-avatar"><?php echo htmlspecialchars($initial); ?></span>
                </button>
                <div class="account-dropdown" id="account-dropdown" hidden>
                    <div class="account-dropdown__head">
                        <strong><?php echo htmlspecialchars($username); ?></strong>
                    </div>
                    <a href="users.php" class="js-my-profile-link">My Profile</a>
                    <a href="logout.php" class="danger">Sign Out</a>
                </div>
            </div>
        </header>

        <section class="customers-page" aria-label="Customer list">
            <div class="customers-page__header">
                <div>
                    <h2>Caf&eacute; Clients</h2>
                </div>
                <div class="header-actions">
                    <input type="search" placeholder="Search customers..." aria-label="Search customers">
                    <select aria-label="Filter by orders">
                        <option>All Orders</option>
                        <option>No Orders</option>
                        <option>1-2 Orders</option>
                        <option>3-5 Orders</option>
                        <option>6-10 Orders</option>
                        <option>11-20 Orders</option>
                        <option>20+ Orders</option>
                    </select>
                </div>
            </div>

            <div class="customers-table__wrapper">
                <table class="customers-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Orders</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="7">No customers yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <?php
                            $orderCount = (int) $customer['orders_count'];
                            $orderBadgeClass = $orderCount >= 20 ? 'orders-badge--pink' : ($orderCount >= 10 ? 'orders-badge--green' : 'orders-badge--muted');
                            ?>
                            <tr>
                                <td><?php echo (int) $customer['id']; ?></td>
                                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                <td><?php echo htmlspecialchars($customer['address']); ?></td>
                                <td>
                                    <span class="orders-badge <?php echo $orderBadgeClass; ?>">
                                        <?php echo $orderCount; ?>&nbsp;orders
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <button
                                        type="button"
                                        class="icon-btn icon-btn--edit js-edit-customer"
                                        aria-label="Edit customer"
                                        data-id="<?php echo (int) $customer['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($customer['name'], ENT_QUOTES); ?>"
                                        data-email="<?php echo htmlspecialchars($customer['email'], ENT_QUOTES); ?>"
                                        data-phone="<?php echo htmlspecialchars($customer['phone'], ENT_QUOTES); ?>"
                                        data-address="<?php echo htmlspecialchars($customer['address'], ENT_QUOTES); ?>"
                                        data-orders="<?php echo (int) $customer['orders_count']; ?>"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 20H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M15.5 4.5L19.5 8.5L10 18H6V14L15.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="icon-btn icon-btn--delete js-delete-customer"
                                        aria-label="Delete customer"
                                        data-id="<?php echo (int) $customer['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($customer['name'], ENT_QUOTES); ?>"
                                        data-email="<?php echo htmlspecialchars($customer['email'], ENT_QUOTES); ?>"
                                    >
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M6 7H18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M10 11V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M14 11V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M5 7L6 19C6 20.1046 6.89543 21 8 21H16C17.1046 21 18 20.1046 18 19L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M9 7V5C9 4.44772 9.44772 4 10 4H14C14.5523 4 15 4.44772 15 5V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php $prevPage = max(1, $currentPage - 1); ?>
                <?php $nextPage = min($totalPages, $currentPage + 1); ?>
                <button
                    type="button"
                    aria-label="Previous page"
                    <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>
                    onclick="window.location.href='?page=<?php echo $prevPage; ?>'"
                >&lt;</button>
                <span>Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?></span>
                <button
                    type="button"
                    aria-label="Next page"
                    <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>
                    onclick="window.location.href='?page=<?php echo $nextPage; ?>'"
                >&gt;</button>
            </div>

            <div class="customers-insights">
                <div class="insight-card chart-card">
                    <h3>Customer Spending Distribution</h3>
                    <div class="chart-wrapper">
                        <div class="chart-area">
                            <canvas id="spendingChart" aria-label="Customer spending distribution chart"></canvas>
                            <div class="chart-center-label">
                                <div class="chart-center-value"><?php echo (int) $stats['totalCustomers']; ?></div>
                                <div class="chart-center-caption">Total Customers</div>
                            </div>
                        </div>
                        <div class="chart-legend" id="spendingLegend"></div>
                    </div>
                </div>
                <div class="insight-metrics">
                    <div class="metric">
                        <div class="dot dot--blue"></div>
                        <div>
                            <p>Total Customers</p>
                            <strong><?php echo (int) $stats['totalCustomers']; ?></strong>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="dot dot--green"></div>
                        <div>
                            <p>Total Revenue Per Customer</p>
                            <strong>₱<?php echo number_format($stats['revenuePerCustomer'], 2); ?></strong>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="dot dot--orange"></div>
                        <div>
                            <p>Avg Customer Per Day</p>
                            <strong><?php echo number_format($stats['customersPerDay'], 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <button id="customers-fab" class="customers-fab" type="button" aria-label="Add new customer">+</button>

            <dialog id="addCustomerModal" class="customers-modal">
                <form id="customer-form" class="customers-modal__card" method="post" action="#" novalidate>
                    <input type="hidden" name="id" id="customer-id" value="">
                    <input type="hidden" name="orders_count" id="customer-orders" value="0">
                    <header class="customers-modal__header">
                        <h3 id="customer-form-title">Add New Customer</h3>
                        <button type="button" class="close-btn" id="closeAddCustomer" aria-label="Close add customer form">&times;</button>
                    </header>
                    <div class="form-grid">
                        <label class="field field--full">
                            <span>Full Name</span>
                            <input type="text" name="full_name" id="customer-name" placeholder="Full Name" required>
                        </label>
                        <label class="field">
                            <span>Email</span>
                            <input type="email" name="email" id="customer-email" placeholder="Email Address" required>
                        </label>
                        <label class="field">
                            <span>Phone</span>
                            <input type="tel" name="phone" id="customer-phone" placeholder="Phone Number" required>
                        </label>
                        <label class="field field--full">
                            <span>Address</span>
                            <input type="text" name="address" id="customer-address" placeholder="Address">
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn--ghost" id="cancelAddCustomer">Cancel</button>
                        <button type="submit" class="btn btn--primary" id="customer-submit-btn">Add Customer</button>
                    </div>
                </form>
            </dialog>

            <dialog id="removeCustomerModal" class="customers-modal">
                <div class="customers-modal__card customers-modal__card--confirm">
                    <header class="customers-modal__header customers-modal__header--danger">
                        <h3>Remove Customer</h3>
                        <button type="button" class="close-btn" id="closeRemoveCustomer" aria-label="Close remove customer dialog">&times;</button>
                    </header>
                    <div class="confirm-body">
                        <p class="confirm-warning">Are you sure you want to remove this customer? This action cannot be undone.</p>
                        <p id="removeCustomerName" class="confirm-name"></p>
                        <p id="removeCustomerEmail" class="confirm-email"></p>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn--ghost" id="cancelRemoveCustomer">Cancel</button>
                        <button type="button" class="btn btn--danger" id="confirmRemoveCustomer">Remove Customer</button>
                    </div>
                </div>
            </dialog>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.customerSpendingData = {
    labels: ['₱0-₱20', '₱20-₱50', '₱50-₱100', 'No Spending', 'Over ₱100'],
    counts: [
        <?php echo (int) $spendingDistribution['₱0-₱20']; ?>,
        <?php echo (int) $spendingDistribution['₱20-₱50']; ?>,
        <?php echo (int) $spendingDistribution['₱50-₱100']; ?>,
        <?php echo (int) $spendingDistribution['No Spending']; ?>,
        <?php echo (int) $spendingDistribution['Over ₱100']; ?>
    ],
    colors: ['#6b35d9', '#e23c7e', '#f0b22f', '#2b90e0', '#28a56b']
};
</script>
<script src="../assets/js/notify.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/notify.js'); ?>"></script>
<script src="../assets/js/custom/customers.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/custom/customers.js'); ?>"></script>
<script src="../assets/js/account-menu.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/account-menu.js'); ?>"></script>
<script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>

