<?php
require_once '../includes/require_admin.php';
require_once '../includes/db.php';

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));

$metrics = [
    'totalSales' => 0.0,
    'totalOrders' => 0,
    'totalCustomers' => 0,
    'avgOrderValue' => 0.0
];
$statusCounts = [
    'Pending' => 0,
    'Processing' => 0,
    'Completed' => 0,
    'Cancelled' => 0
];
$popularItems = [];
$recentOrders = [];

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

    $metricQuery = $conn->query(
        "SELECT
            COALESCE(SUM(total), 0) AS total_sales,
            COUNT(*) AS total_orders,
            COALESCE(AVG(total), 0) AS avg_order_value
         FROM orders"
    );
    if ($metricQuery) {
        $metricRow = $metricQuery->fetch_assoc();
        $metrics['totalSales'] = (float) ($metricRow['total_sales'] ?? 0);
        $metrics['totalOrders'] = (int) ($metricRow['total_orders'] ?? 0);
        $metrics['avgOrderValue'] = (float) ($metricRow['avg_order_value'] ?? 0);
        $metricQuery->free();
    }

    $customerCountQuery = $conn->query("SELECT COUNT(*) AS total_customers FROM customers");
    if ($customerCountQuery) {
        $customerCountRow = $customerCountQuery->fetch_assoc();
        $metrics['totalCustomers'] = (int) ($customerCountRow['total_customers'] ?? 0);
        $customerCountQuery->free();
    }

    $statusQuery = $conn->query("SELECT status, COUNT(*) AS total FROM orders GROUP BY status");
    if ($statusQuery) {
        while ($statusRow = $statusQuery->fetch_assoc()) {
            $statusName = $statusRow['status'];
            if (isset($statusCounts[$statusName])) {
                $statusCounts[$statusName] = (int) $statusRow['total'];
            }
        }
        $statusQuery->free();
    }

    $recentOrderQuery = $conn->query("SELECT id, customer_name, items, total, status FROM orders ORDER BY id DESC LIMIT 5");
    if ($recentOrderQuery) {
        while ($orderRow = $recentOrderQuery->fetch_assoc()) {
            $recentOrders[] = [
                'id' => (int) $orderRow['id'],
                'customer' => $orderRow['customer_name'],
                'items' => $orderRow['items'],
                'total' => (float) $orderRow['total'],
                'status' => $orderRow['status']
            ];
        }
        $recentOrderQuery->free();
    }

    $itemCounts = [];
    $orderItemsQuery = $conn->query("SELECT items FROM orders");
    if ($orderItemsQuery) {
        while ($itemsRow = $orderItemsQuery->fetch_assoc()) {
            $rawItems = trim((string) ($itemsRow['items'] ?? ''));
            if ($rawItems === '') {
                continue;
            }

            $tokens = array_filter(array_map('trim', explode(',', $rawItems)));
            foreach ($tokens as $token) {
                if (preg_match('/^(\d+)\s*x\s*(.+?)\s*\(\$?[\d.]+\)$/i', $token, $match)) {
                    $qty = max(1, (int) $match[1]);
                    $name = trim($match[2]);
                } else {
                    $qty = 1;
                    $name = trim(preg_replace('/\s*\(\$?[\d.]+\)\s*/', '', $token));
                }

                if ($name === '') {
                    continue;
                }

                if (!isset($itemCounts[$name])) {
                    $itemCounts[$name] = 0;
                }
                $itemCounts[$name] += $qty;
            }
        }
        $orderItemsQuery->free();
    }

    arsort($itemCounts);
    $popularItems = array_slice($itemCounts, 0, 5, true);

    if ($conn && $conn instanceof mysqli) {
        $conn->close();
    }
}

$popularLabels = array_values(array_map('strval', array_keys($popularItems)));
$popularValues = array_values(array_map('intval', array_values($popularItems)));
if (empty($popularLabels)) {
    $popularLabels = ['No items yet'];
    $popularValues = [0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | IKOMERS KOPI</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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
                        <a href="dashboard.php" aria-current="page">
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
            <a class="logout-link" href="logout.php">Log out</a>
            <span>Signed in as <strong><?php echo htmlspecialchars($username); ?></strong></span>
        </div>
    </aside>

    <main class="main-area" role="main">
        <header class="main-header">
            <div class="main-header__title">
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

        <section class="empty-state" aria-label="Dashboard placeholders">
            <div class="under-header">
                <div class="cards">
                    <p>Total Sales</p>
                    <h3>$<?php echo number_format($metrics['totalSales'], 2); ?></h3>
                </div>
                <div class="cards">
                    <p>Orders</p>
                    <h3><?php echo (int) $metrics['totalOrders']; ?></h3>
                </div>
                <div class="cards">
                    <p>Customers</p>
                    <h3><?php echo (int) $metrics['totalCustomers']; ?></h3>
                </div>
                <div class="cards">
                    <p>Avg. Order Value</p>
                    <h3>$<?php echo number_format($metrics['avgOrderValue'], 2); ?></h3>
                </div>
            </div>

            <div class="graph-container">
                <div class="graph-card chart-card">
                    <header class="chart-card__header">
                        <h3>Orders Status</h3>
                    </header>
                    <div class="chart-card__body">
                        <div class="chart-legend" id="order-status-legend"></div>
                        <div class="chart-area">
                            <canvas id="orderStatusChart" class="chart-canvas" aria-label="Order status chart"></canvas>
                            <div class="chart-center-label">
                                <div id="orderStatusTotal" class="chart-center-value"><?php echo (int) $metrics['totalOrders']; ?></div>
                                <div class="chart-center-caption">Total Orders</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="graph-card chart-card">
                    <header class="chart-card__header">
                        <h3>Popular Items</h3>
                    </header>
                    <div class="chart-card__body chart-card__body--single">
                        <canvas id="popularItemsChart" class="chart-canvas" aria-label="Popular items chart"></canvas>
                    </div>
                </div>
            </div>

            <div class="recent-orders-container">
                <h3>Recent Orders</h3>
                <table class="recent-orders">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="5">No orders yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <?php $statusClass = strtolower($order['status']); ?>
                                <tr>
                                    <td>#<?php echo (int) $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer']); ?></td>
                                    <td><?php echo htmlspecialchars($order['items']); ?></td>
                                    <td>$<?php echo number_format((float) $order['total'], 2); ?></td>
                                    <td><span class="status-pill status-pill--<?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['status']); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.dashboardData = {
    orderStatus: {
        labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
        counts: [
            <?php echo (int) $statusCounts['Pending']; ?>,
            <?php echo (int) $statusCounts['Processing']; ?>,
            <?php echo (int) $statusCounts['Completed']; ?>,
            <?php echo (int) $statusCounts['Cancelled']; ?>
        ],
        colors: ['#4532d3', '#17b7b2', '#f16521', '#8d3cf0']
    },
    popularItems: {
        labels: <?php echo json_encode($popularLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        counts: <?php echo json_encode($popularValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
        colors: ['#5b6c87', '#c28462', '#6c7570', '#b28c73', '#7aa36f']
    }
};

(function renderDashboardCharts() {
    if (typeof Chart === 'undefined' || !window.dashboardData) {
        return;
    }

    const orderStatusData = window.dashboardData.orderStatus;
    const orderStatusCtx = document.getElementById('orderStatusChart');
    const orderStatusTotalEl = document.getElementById('orderStatusTotal');

    if (orderStatusCtx) {
        orderStatusCtx.classList.remove('chart-canvas--spin');
        void orderStatusCtx.offsetWidth;
        orderStatusCtx.classList.add('chart-canvas--spin');

        new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: orderStatusData.labels,
                datasets: [{
                    data: orderStatusData.counts,
                    backgroundColor: orderStatusData.colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: { legend: { display: false } }
            }
        });

        const totalOrders = orderStatusData.counts.reduce((a, b) => a + b, 0);
        if (orderStatusTotalEl) {
            orderStatusTotalEl.textContent = totalOrders;
        }
        const legend = document.getElementById('order-status-legend');
        if (legend) {
            legend.innerHTML = orderStatusData.labels.map((label, idx) => {
                const count = Number(orderStatusData.counts[idx] || 0);
                const pct = totalOrders > 0 ? ((count / totalOrders) * 100).toFixed(1) : '0.0';
                return `<div class="legend-item">
                            <span class="legend-swatch" style="background:${orderStatusData.colors[idx]}"></span>
                            <span class="legend-label">${label}</span>
                            <span class="legend-value">${count} (${pct}%)</span>
                        </div>`;
            }).join('');
        }
    }

    const popularItemsData = window.dashboardData.popularItems;
    const popularItemsCtx = document.getElementById('popularItemsChart');
    if (popularItemsCtx) {
        const barColors = popularItemsData.labels.map((_, idx) => popularItemsData.colors[idx % popularItemsData.colors.length]);
        new Chart(popularItemsCtx, {
            type: 'bar',
            data: {
                labels: popularItemsData.labels,
                datasets: [{
                    data: popularItemsData.counts,
                    backgroundColor: barColors,
                    borderRadius: 10,
                    barPercentage: 0.8,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b5c52', font: { weight: '600' }, maxRotation: 0, minRotation: 0 }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e7dccd' },
                        ticks: { color: '#6b5c52', stepSize: 1 }
                    }
                }
            }
        });
    }
})();
</script>
<script src="../assets/js/notify.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/notify.js'); ?>"></script>
<script src="../assets/js/account-menu.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/account-menu.js'); ?>"></script>
<script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>
