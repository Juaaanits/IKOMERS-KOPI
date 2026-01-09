<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));

// Temporary static data to illustrate the UI.
$customers = [
    [
        'id' => 85,
        'name' => 'Marcus Johnson',
        'email' => 'mjohnson.business@email.net',
        'phone' => '(617) 555-9021',
        'address' => '463 Commonwealth Ave, Boston, MA',
        'orders' => 18
    ],
    [
        'id' => 84,
        'name' => "William O'Connor",
        'email' => 'woconnor55@email.net',
        'phone' => '(702) 555-1234',
        'address' => '567 Desert Palm Dr, Las Vegas, NV',
        'orders' => 26
    ],
    [
        'id' => 83,
        'name' => 'Maria Sanchez',
        'email' => 'msanchez.2024@email.com',
        'phone' => '(512) 555-4567',
        'address' => '3201 River Road, Austin, TX',
        'orders' => 20
    ]
];

$stats = [
    'totalCustomers' => 16,
    'revenuePerCustomer' => 63.64,
    'customersPerDay' => 4.67
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers | IKOMERS KOPI</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/customers.css">
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
                <h1>Dashboard</h1>
            </div>
            <div class="account-pill">
                <span>Hello, <?php echo htmlspecialchars($username); ?></span>
                <span class="account-avatar"><?php echo htmlspecialchars($initial); ?></span>
            </div>
        </header>

        <section class="customers-page" aria-label="Customer list">
            <div class="customers-page__header">
                <div>
                    <p class="eyebrow">Cafe Clients</p>
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
                    <?php foreach ($customers as $customer): ?>
                        <?php
                        $orderCount = (int) $customer['orders'];
                        $orderBadgeClass = $orderCount >= 20 ? 'orders-badge--pink' : ($orderCount >= 10 ? 'orders-badge--green' : 'orders-badge--muted');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['id']); ?></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['address']); ?></td>
                            <td>
                                <span class="orders-badge <?php echo $orderBadgeClass; ?>">
                                    <?php echo htmlspecialchars($orderCount); ?> orders
                                </span>
                            </td>
                            <td class="actions-cell">
                                <button type="button" class="icon-btn icon-btn--edit" aria-label="Edit customer">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 20H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M15.5 4.5L19.5 8.5L10 18H6V14L15.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button type="button" class="icon-btn icon-btn--delete" aria-label="Delete customer" data-name="<?php echo htmlspecialchars($customer['name']); ?>" data-email="<?php echo htmlspecialchars($customer['email']); ?>">
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
                <span>Page 1 of 4</span>
                <button type="button" aria-label="Next page">&gt;</button>
            </div>

            <div class="customers-insights">
                <div class="insight-card chart-card">
                    <h3>Customer Spending Distribution</h3>
                    <div class="chart-wrapper">
                        <div class="chart-area">
                            <canvas id="spendingChart" aria-label="Customer spending distribution chart"></canvas>
                            <div class="chart-center-label">
                                <div class="chart-center-value">16</div>
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
                            <strong><?php echo $stats['totalCustomers']; ?></strong>
                        </div>
                    </div>
                    <div class="metric">
                        <div class="dot dot--green"></div>
                        <div>
                            <p>Total Revenue Per Customer</p>
                            <strong>$<?php echo number_format($stats['revenuePerCustomer'], 2); ?></strong>
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
                <form class="customers-modal__card" method="post" action="#" novalidate>
                    <header class="customers-modal__header">
                        <h3>Add New Customer</h3>
                        <button type="button" class="close-btn" id="closeAddCustomer" aria-label="Close add customer form">×</button>
                    </header>
                    <div class="form-grid">
                        <label class="field field--full">
                            <span>Full Name</span>
                            <input type="text" name="full_name" placeholder="Full Name" required>
                        </label>
                        <label class="field">
                            <span>Email</span>
                            <input type="email" name="email" placeholder="Email Address" required>
                        </label>
                        <label class="field">
                            <span>Phone</span>
                            <input type="tel" name="phone" placeholder="Phone Number" required>
                        </label>
                        <label class="field field--full">
                            <span>Address</span>
                            <input type="text" name="address" placeholder="Address">
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn--ghost" id="cancelAddCustomer">Cancel</button>
                        <button type="submit" class="btn btn--primary">Add Customer</button>
                    </div>
                </form>
            </dialog>

            <dialog id="removeCustomerModal" class="customers-modal">
                <div class="customers-modal__card customers-modal__card--confirm">
                    <header class="customers-modal__header customers-modal__header--danger">
                        <h3>Remove Customer</h3>
                        <button type="button" class="close-btn" id="closeRemoveCustomer" aria-label="Close remove customer dialog">×</button>
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
<script src="../assets/js/custom/customers.js"></script>
</body>
</html>
