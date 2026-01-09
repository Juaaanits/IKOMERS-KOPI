<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));

// Sample data to mirror the reference UI
$orders = [
    [
        'id' => 132,
        'customer' => 'Test Name',
        'items' => '1x Cappuccino ($4.75)',
        'total' => 4.75,
        'status' => 'Processing',
        'date' => '2025-01-06 11:00',
    ],
    [
        'id' => 131,
        'customer' => 'Maria Sanchez',
        'items' => '1x Cappuccino ($4.75)',
        'total' => 4.75,
        'status' => 'Completed',
        'date' => '2025-01-05 11:00',
    ],
    [
        'id' => 130,
        'customer' => 'David Kim',
        'items' => '22x Americano ($3.75)',
        'total' => 82.50,
        'status' => 'Completed',
        'date' => '2025-01-05 00:30',
    ],
    [
        'id' => 129,
        'customer' => 'Michael Bennett',
        'items' => '1x Cappuccino ($4.75)',
        'total' => 4.75,
        'status' => 'Cancelled',
        'date' => '2025-01-05 00:10',
    ]
];

$statusCounts = [
    'Pending' => 54,
    'Processing' => 30,
    'Completed' => 42,
    'Cancelled' => 5
];

$metrics = [
    'totalOrders' => 131,
    'totalRevenue' => 869.00,
    'avgOrderValue' => 6.90
];
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
            <div class="account-pill">
                <span>Hello, <?php echo htmlspecialchars($username); ?></span>
                <span class="account-avatar"><?php echo htmlspecialchars($initial); ?></span>
            </div>
        </header>

        <section class="orders-page" aria-label="Orders">
            <div class="orders-header">
                <div>
                    <p class="eyebrow">Cafe Orders</p>
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
                                <button type="button" class="icon-btn icon-btn--view" aria-label="View order">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 12C3 12 6.5 6 12 6C17.5 6 21 12 21 12C21 12 17.5 18 12 18C6.5 18 3 12 3 12Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>
                                    </svg>
                                </button>
                                <button type="button" class="icon-btn icon-btn--edit status-edit-btn" data-order="<?php echo htmlspecialchars($order['id']); ?>" data-status="<?php echo htmlspecialchars($order['status']); ?>" aria-label="Edit order">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 20H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M15.5 4.5L19.5 8.5L10 18H6V14L15.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button type="button" class="icon-btn icon-btn--delete" aria-label="Delete order">
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
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <button type="button" aria-label="Previous page">&lt;</button>
                <span>Page 1 of 27</span>
                <button type="button" aria-label="Next page">&gt;</button>
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
                <form class="orders-modal__card" method="post" action="#" novalidate>
                    <header class="orders-modal__header">
                        <h3>Add New Order</h3>
                        <button type="button" class="close-btn" id="closeOrderModal" aria-label="Close add order form">×</button>
                    </header>
                    <div class="form-grid">
                        <label class="field customer-field field--full">
                            <span>Customer</span>
                            <div class="customer-row">
                                <input type="text" placeholder="Customer Name" required>
                                <input type="text" class="id-chip" placeholder="ID: #####" aria-label="Customer ID" disabled>
                                <button type="button" class="icon-btn icon-btn--view" id="openSelectCustomer" aria-label="Select customer">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 17L14 21L22 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M20 21H4C2.89543 21 2 20.1046 2 19V5C2 3.89543 2.89543 3 4 3H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </label>
                        <div class="field field--full">
                            <span>Items</span>
                            <button type="button" class="add-item-btn">Add Item</button>
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
                                        <td>Black Coffee</td>
                                        <td>11</td>
                                        <td>4</td>
                                        <td>3.75</td>
                                        <td>15.00</td>
                                        <td><button type="button" class="icon-btn icon-btn--delete" aria-label="Remove item">&times;</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <label class="field">
                            <span>Total</span>
                            <input type="text" placeholder="Total Amount" value="$15.00" disabled>
                        </label>
                        <label class="field">
                            <span>Status</span>
                            <select>
                                <option>Pending</option>
                                <option>Processing</option>
                                <option>Completed</option>
                                <option>Cancelled</option>
                            </select>
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn--ghost" id="cancelOrderModal">Cancel</button>
                        <button type="submit" class="btn btn--primary">Add Order</button>
                    </div>
                </form>
            </dialog>

            <dialog id="selectCustomerModal" class="orders-modal orders-modal--wide">
                <div class="orders-modal__card">
                    <header class="orders-modal__header">
                        <h3>Select Customer</h3>
                        <button type="button" class="close-btn" id="closeSelectCustomer" aria-label="Close select customer dialog">×</button>
                    </header>
                    <div class="field field--full">
                        <input type="search" placeholder="Search customers..." aria-label="Search customers">
                    </div>
                    <div class="customers-list">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $customers = [
                                    ['id' => 87, 'name' => 'Test Name', 'email' => 'test@mail.com', 'phone' => '111 222 333'],
                                    ['id' => 85, 'name' => 'Marcus Johnson', 'email' => 'mjohnson.business@email.net', 'phone' => '(617) 555-9021'],
                                    ['id' => 84, 'name' => "William O'Connor", 'email' => 'woconnor55@email.net', 'phone' => '(702) 555-1234'],
                                    ['id' => 83, 'name' => 'Maria Sanchez', 'email' => 'msanchez.2024@email.com', 'phone' => '(512) 555-4567'],
                                    ['id' => 82, 'name' => 'David Kim', 'email' => 'dkim_personal@email.com', 'phone' => '(404) 555-8901'],
                                ];
                                foreach ($customers as $c): ?>
                                    <tr>
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
                        <button type="button" class="btn btn--ghost" id="cancelSelectCustomer">Cancel</button>
                        <button type="button" class="btn btn--primary">Select Customer</button>
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
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="../assets/js/custom/orders.js"></script>
</body>
</html>
