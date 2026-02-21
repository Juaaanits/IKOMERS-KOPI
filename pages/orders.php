<?php
require_once '../includes/require_admin.php';
require_once '../includes/db.php';

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));

$statusCounts = [
    'Pending' => 0,
    'Processing' => 0,
    'Completed' => 0,
    'Cancelled' => 0
];

$orders = [];
$menuCatalog = [];
$customerOptions = [];
$orderResult = '';
$orderResultType = '';
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

    $customerListResult = $conn->query("SELECT id, name, email, phone FROM customers ORDER BY id DESC");
    if ($customerListResult) {
        while ($customerRow = $customerListResult->fetch_assoc()) {
            $customerOptions[] = [
                'id' => (int) $customerRow['id'],
                'name' => $customerRow['name'],
                'email' => $customerRow['email'],
                'phone' => $customerRow['phone']
            ];
        }
        $customerListResult->free();
    }

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

    $menuResult = $conn->query("SELECT id, name, price FROM menu_items ORDER BY name ASC");
    if ($menuResult) {
        while ($row = $menuResult->fetch_assoc()) {
            $menuCatalog[] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'price' => (float) $row['price']
            ];
        }
        $menuResult->free();
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $customerName = trim($_POST['customer_name'] ?? '');
    $items = trim($_POST['items'] ?? '');
    $total = trim($_POST['total'] ?? '');
    $status = trim($_POST['status'] ?? 'Pending');
    $allowedStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];

    if (!$dbReady) {
        $orderResult = 'Database is unavailable. Please try again later.';
        $orderResultType = 'error';
    } elseif ($customerName === '') {
        $orderResult = 'Customer name is required.';
        $orderResultType = 'error';
    } elseif ($items === '') {
        $orderResult = 'Order items are required.';
        $orderResultType = 'error';
    } elseif (!is_numeric($total) || (float) $total <= 0) {
        $orderResult = 'Total must be a number greater than 0.';
        $orderResultType = 'error';
    } elseif (!in_array($status, $allowedStatuses, true)) {
        $orderResult = 'Invalid order status.';
        $orderResultType = 'error';
    } else {
        $totalValue = (float) $total;

        if ($orderId > 0) {
            $update = $conn->prepare('UPDATE orders SET customer_name = ?, items = ?, total = ?, status = ? WHERE id = ?');
            if ($update) {
                $update->bind_param('ssdsi', $customerName, $items, $totalValue, $status, $orderId);
                if ($update->execute()) {
                    $orderResult = 'Order updated successfully.';
                    $orderResultType = 'success';
                } else {
                    $orderResult = 'Failed to update order.';
                    $orderResultType = 'error';
                }
                $update->close();
            } else {
                $orderResult = 'Unable to update order right now.';
                $orderResultType = 'error';
            }
        } else {
            $insert = $conn->prepare('INSERT INTO orders (customer_name, items, total, status) VALUES (?, ?, ?, ?)');
            if ($insert) {
                $insert->bind_param('ssds', $customerName, $items, $totalValue, $status);
                if ($insert->execute()) {
                    $orderResult = 'Order added successfully.';
                    $orderResultType = 'success';
                } else {
                    $orderResult = 'Failed to add order.';
                    $orderResultType = 'error';
                }
                $insert->close();
            } else {
                $orderResult = 'Unable to save order right now.';
                $orderResultType = 'error';
            }
        }
    }
}

if ($dbReady) {
    $metricsResult = $conn->query("
        SELECT
            COUNT(*) AS total_orders,
            COALESCE(SUM(total), 0) AS total_revenue,
            COALESCE(AVG(total), 0) AS avg_order_value
        FROM orders
    ");
    if ($metricsResult) {
        $metricsRow = $metricsResult->fetch_assoc();
        $metrics = [
            'totalOrders' => (int) ($metricsRow['total_orders'] ?? 0),
            'totalRevenue' => (float) ($metricsRow['total_revenue'] ?? 0),
            'avgOrderValue' => (float) ($metricsRow['avg_order_value'] ?? 0)
        ];
        $metricsResult->free();
    } else {
        $metrics = [
            'totalOrders' => 0,
            'totalRevenue' => 0.0,
            'avgOrderValue' => 0.0
        ];
    }

    $totalPages = max(1, (int) ceil($metrics['totalOrders'] / $perPage));
    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }

    $statusResult = $conn->query("SELECT status, COUNT(*) AS total FROM orders GROUP BY status");
    if ($statusResult) {
        while ($statusRow = $statusResult->fetch_assoc()) {
            $statusName = $statusRow['status'];
            if (isset($statusCounts[$statusName])) {
                $statusCounts[$statusName] = (int) $statusRow['total'];
            }
        }
        $statusResult->free();
    }

    $offset = ($currentPage - 1) * $perPage;
    $orderStmt = $conn->prepare('SELECT id, customer_name, items, total, status, ordered_at FROM orders ORDER BY id DESC LIMIT ? OFFSET ?');
    if ($orderStmt) {
        $orderStmt->bind_param('ii', $perPage, $offset);
        $orderStmt->execute();
        $orderResultSet = $orderStmt->get_result();
        if ($orderResultSet) {
            while ($row = $orderResultSet->fetch_assoc()) {
                $orders[] = [
                    'id' => (int) $row['id'],
                    'customer' => $row['customer_name'],
                    'items' => $row['items'],
                    'total' => (float) $row['total'],
                    'status' => $row['status'],
                    'date' => date('Y-m-d H:i', strtotime($row['ordered_at']))
                ];
            }
            $orderResultSet->free();
        }
        $orderStmt->close();
    }
} else {
    $metrics = [
        'totalOrders' => 0,
        'totalRevenue' => 0.0,
        'avgOrderValue' => 0.0
    ];
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
    <title>Orders | IKOMERS KOPI</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/orders.css">
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
                    <a href="orders.php" aria-current="page">
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
            <a class="logout-link" href="logout.php">Log out</a>
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

        <section class="orders-page" aria-label="Orders">
            <?php if ($orderResult !== ''): ?>
                <div class="menu-feedback menu-feedback--<?php echo $orderResultType === 'success' ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($orderResult); ?>
                </div>
            <?php endif; ?>
            <div class="orders-header">
                <div>
                    <h2>Caf&eacute; Orders</h2>
                </div>
                <div class="filters">
                    <input type="search" placeholder="Search orders..." aria-label="Search orders">
                    <select aria-label="Filter orders by status">
                        <option>All</option>
                        <option>Pending</option>
                        <option>Processing</option>
                        <option>Completed</option>
                        <option>Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="orders-table__wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7">No orders yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $statusClass = strtolower($order['status']);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer']); ?></td>
                                <td><?php echo htmlspecialchars($order['items']); ?></td>
                                <td><?php echo number_format($order['total'], 2); ?></td>
                                <td><span class="status-pill status-pill--<?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($order['date']); ?></td>
                                <td class="actions-cell">
                                    <button
                                        type="button"
                                        class="icon-btn icon-btn--view js-view-order"
                                        aria-label="View order"
                                        data-id="<?php echo (int) $order['id']; ?>"
                                        data-customer="<?php echo htmlspecialchars($order['customer'], ENT_QUOTES); ?>"
                                        data-items="<?php echo htmlspecialchars($order['items'], ENT_QUOTES); ?>"
                                        data-total="<?php echo htmlspecialchars(number_format((float) $order['total'], 2, '.', ''), ENT_QUOTES); ?>"
                                        data-date="<?php echo htmlspecialchars($order['date'], ENT_QUOTES); ?>"
                                        data-status="<?php echo htmlspecialchars($order['status'], ENT_QUOTES); ?>"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M3 12C3 12 6.5 6 12 6C17.5 6 21 12 21 12C21 12 17.5 18 12 18C6.5 18 3 12 3 12Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        class="icon-btn icon-btn--edit js-edit-order"
                                        data-order-id="<?php echo (int)$order['id']; ?>"
                                        data-customer="<?php echo htmlspecialchars($order['customer'], ENT_QUOTES); ?>"
                                        data-items="<?php echo htmlspecialchars($order['items'], ENT_QUOTES); ?>"
                                        data-total="<?php echo htmlspecialchars(number_format((float)$order['total'], 2, '.', ''), ENT_QUOTES); ?>"
                                        data-status="<?php echo htmlspecialchars($order['status'], ENT_QUOTES); ?>"
                                        aria-label="Edit order"
                                        >

                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4 20H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M15.5 4.5L19.5 8.5L10 18H6V14L15.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="icon-btn icon-btn--delete js-delete-order" data-order-id="<?php echo (int) $order['id']; ?>" aria-label="Delete order">
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

            <div class="orders-insights">
                <div class="insight-card chart-card">
                    <h3>Orders Status</h3>
                    <div class="chart-wrapper">
                        <div class="chart-area">
                            <canvas id="orderStatusChart" aria-label="Orders status chart"></canvas>
                            <div class="chart-center-label">
                                <div class="chart-center-value"><?php echo $metrics['totalOrders']; ?></div>
                                <div class="chart-center-caption">Total Orders</div>
                            </div>
                        </div>
                        <div class="chart-legend" id="orderStatusLegend"></div>
                    </div>
                </div>
                <div class="insight-metrics">
                    <div class="metric">
                        <div class="dot dot--blue"></div>
                        <div>
                            <p>Total Orders</p>
                            <strong><?php echo $metrics['totalOrders']; ?></strong>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="dot dot--green"></div>
                        <div>
                            <p>Total Revenue</p>
                            <strong>$<?php echo number_format($metrics['totalRevenue'], 2); ?></strong>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="dot dot--orange"></div>
                        <div>
                            <p>Avg Order Value</p>
                            <strong>$<?php echo number_format($metrics['avgOrderValue'], 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <button id="orders-fab" class="orders-fab" type="button" aria-label="Add new order">+</button>

            <dialog id="orderModal" class="orders-modal">
                <form class="orders-modal__card order-create-card" method="post" action="#" novalidate>
                    <header class="orders-modal__header">
                        <h3>Add New Order</h3>
                        <button type="button" class="close-btn" id="closeOrderModal" aria-label="Close add order form">&times;</button>
                    </header>
                            
                    <input type="hidden" name="order_id" id="order-id" value="">

                    <div class="order-create-grid">
                        <label class="field customer-field field--full">
                            <span>Customer:</span>
                            <div class="customer-row">
                                <input type="text" id="order-customer-name" name="customer_name" placeholder="Customer Name" required>
                                <input type="text" id="order-customer-id" class="id-chip" placeholder="ID: #####" aria-label="Customer ID" disabled>
                                <button type="button" class="icon-btn icon-btn--search" id="openSelectCustomer" aria-label="Select customer">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                        </label>

                        <div class="field field--full">
                            <span>Items:</span>
                            <button type="button" class="add-item-btn" id="addOrderItemBtn">Add Item</button>
                            <div class="order-items-box">
                                <table class="items-table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>ID</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="items-empty-row"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <datalist id="order-item-options">
                                <?php foreach ($menuCatalog as $menuItem): ?>
                                    <option value="<?php echo htmlspecialchars($menuItem['name']); ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                            <textarea class="order-items-hidden" name="items" rows="3" placeholder="Example: 2x Cappuccino ($4.75), 1x Latte ($4.50)" required></textarea>
                        </div>

                        <label class="field field--half">
                            <span>Total:</span>
                            <input type="number" name="total" step="0.01" min="0.01" placeholder="Total Amount" required>
                        </label>

                        <label class="field field--full">
                            <span>Status:</span>
                            <select name="status">
                                <option>Pending</option>
                                <option>Processing</option>
                                <option>Completed</option>
                                <option>Cancelled</option>
                            </select>
                        </label>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn--ghost" id="cancelOrderModal">Cancel</button>
                        <button type="submit" class="btn btn--danger-submit" name="add_order" value="1">Add Order</button>
                    </div>
                </form>
            </dialog>

            <dialog id="selectCustomerModal" class="orders-modal orders-modal--wide">
                <div class="orders-modal__card select-customer-card">
                    <header class="orders-modal__header select-customer-header">
                        <h3>Select Customer</h3>
                        <button type="button" class="close-btn" id="closeSelectCustomer" aria-label="Close select customer dialog">&times;</button>
                    </header>
                    <div class="field field--full select-customer-search">
                        <input type="search" id="select-customer-search-input" placeholder="Search customers..." aria-label="Search customers">
                    </div>
                    <div class="customers-list">
                        <table id="select-customer-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customerOptions as $c): ?>
                                    <tr class="select-customer-row" data-id="<?php echo (int) $c['id']; ?>" data-name="<?php echo htmlspecialchars($c['name'], ENT_QUOTES); ?>">
                                        <td><?php echo $c['id']; ?></td>
                                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                                        <td><?php echo htmlspecialchars($c['phone']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn--danger-submit" id="cancelSelectCustomer">Cancel</button>
                        <button type="button" class="btn btn--primary" id="confirmSelectCustomer">Select Customer</button>
                    </div>
                </div>
            </dialog>

            <dialog id="statusModal" class="orders-modal">
                <div class="orders-modal__card">
                    <header class="orders-modal__header">
                        <h3>Edit Order Status</h3>
                        <button type="button" class="close-btn" id="closeStatusModal" aria-label="Close status dialog">×</button>
                    </header>
                    <div class="field field--full">
                        <span id="statusOrderLabel">Order: #132</span>
                        <select id="statusSelect">
                            <option>Pending</option>
                            <option>Processing</option>
                            <option>Completed</option>
                            <option>Cancelled</option>
                        </select>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn--ghost" id="cancelStatusModal">Cancel</button>
                        <button type="button" class="btn btn--primary" id="saveStatusModal">Save</button>
                    </div>
                </div>
            </dialog>

            <dialog id="viewOrderModal" class="orders-modal">
                <div class="orders-modal__card order-view-card">
                    <header class="orders-modal__header">
                        <div>
                            <h3 id="viewOrderTitle">Order #0</h3>
                            <p id="viewOrderDate" class="order-view-date"></p>
                        </div>
                        <button type="button" class="close-btn" id="closeViewOrderModal" aria-label="Close view order dialog">&times;</button>
                    </header>

                    <div class="order-view-section">
                        <h4>Order Items</h4>
                        <ul id="viewOrderItems" class="order-view-items"></ul>
                        <p class="order-view-subtotal">Subtotal <span id="viewOrderSubtotal">$0.00</span></p>
                    </div>

                    <div class="order-view-footer">
                        <div class="order-view-total">Total <strong id="viewOrderTotal">$0.00</strong></div>
                        <button type="button" class="btn btn--print" id="printViewOrderBtn">Print Order</button>
                        <button type="button" class="btn btn--close-order" id="cancelViewOrderModal">Close</button>
                    </div>
                </div>
            </dialog>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.menuItemCatalog = <?php echo json_encode($menuCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
window.ordersStatusData = {
    labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
    values: [
        <?php echo (int) $statusCounts['Pending']; ?>,
        <?php echo (int) $statusCounts['Processing']; ?>,
        <?php echo (int) $statusCounts['Completed']; ?>,
        <?php echo (int) $statusCounts['Cancelled']; ?>
    ],
    colors: ['#6b35d9', '#17b7b2', '#f16521', '#c23dc4']
};
</script>
<script src="../assets/js/notify.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/notify.js'); ?>"></script>
<script src="../assets/js/custom/orders.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/custom/orders.js'); ?>"></script>
<script src="../assets/js/account-menu.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/account-menu.js'); ?>"></script>
<script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>
