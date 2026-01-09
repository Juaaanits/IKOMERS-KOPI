<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));
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
                <h1>Dashboard</h1>
            </div>
            <div class="account-pill">
                <span>Hello, <?php echo htmlspecialchars($username); ?></span>
                <span class="account-avatar"><?php echo htmlspecialchars($initial); ?></span>
            </div>
        </header>

        <section class="empty-state" aria-label="Dashboard placeholders">
            <div class="under-header">
                <div class="cards">
                    <p>Total Sales</p>
                    <h3>$869.0</h3>
                </div>
                <div class="cards">
                    <p>Orders</p>
                    <h3>131</h3>
                </div>
                <div class="cards">
                    <p>Customers</p>
                    <h3>16</h3>
                </div>
                <div class="cards">
                    <p>Avg. Order Value</p>
                    <h3>$6.90</h3>
                </div>
            </div>
            
            <!-- Second row: Orders status + Popular items -->
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
                                <div id="orderStatusTotal" class="chart-center-value">131</div>
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


            <!-- Third row: Recent Orders -->
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
                        <tr>
                            <td>#101</td>
                            <td>John Doe</td>
                            <td>1x Cappuccino ($4.75)</td>
                            <td>$4.75</td>
                            <td>Completed</td>
                        </tr>
                        <tr>
                            <td>#102</td>
                            <td>Jane Smith</td>
                            <td>22x Americano ($3.75)</td>
                            <td>$82.50</td>
                            <td>Pending</td>
                        </tr>
                        <!-- Add more rows -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Orders status doughnut
    const orderStatusData = {
        labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
        counts: [54, 30, 42, 5],
        colors: ['#4532d3', '#17b7b2', '#f16521', '#8d3cf0']
    };

    const orderStatusCtx = document.getElementById('orderStatusChart');
    const orderStatusTotalEl = document.getElementById('orderStatusTotal');
    if (orderStatusCtx) {
        // Trigger CSS spin on the donut only (center label stays static)
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
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 900,
                    easing: 'easeOutCubic'
                },
                animations: {
                    rotation: {
                        from: -4 * Math.PI,
                        to: 0,
                        duration: 900,
                        easing: 'easeOutCubic'
                    },
                    circumference: {
                        from: 0,
                        to: 2 * Math.PI,
                        duration: 900,
                        easing: 'easeOutCubic'
                    },
                    radius: {
                        from: 0,
                        duration: 650,
                        easing: 'easeOutBack'
                    }
                },
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.label}: ${ctx.parsed} (${((ctx.parsed / ctx.chart._metasets[0].total) * 100).toFixed(1)}%)`
                        }
                    }
                }
            }
        });

        const totalOrders = orderStatusData.counts.reduce((a, b) => a + b, 0);
        if (orderStatusTotalEl) {
            orderStatusTotalEl.textContent = totalOrders;
        }
        const legend = document.getElementById('order-status-legend');
        if (legend) {
            legend.innerHTML = orderStatusData.labels.map((label, idx) => {
                const count = orderStatusData.counts[idx];
                const pct = ((count / totalOrders) * 100).toFixed(1);
                return `<div class="legend-item">
                            <span class="legend-swatch" style="background:${orderStatusData.colors[idx]}"></span>
                            <span class="legend-label">${label}</span>
                            <span class="legend-value">${count} (${pct}%)</span>
                        </div>`;
            }).join('');
        }
    }

    // Popular items bar chart
    const popularItemsData = {
        labels: ['Americano', 'Cappuccino', 'Black Coffee', 'Latte', 'Espresso'],
        counts: [31, 18, 16, 15, 12],
        colors: ['#5b6c87', '#c28462', '#6c7570', '#b28c73', '#7aa36f']
    };

    const popularItemsCtx = document.getElementById('popularItemsChart');
    if (popularItemsCtx) {
        new Chart(popularItemsCtx, {
            type: 'bar',
            data: {
                labels: popularItemsData.labels,
                datasets: [{
                    data: popularItemsData.counts,
                    backgroundColor: popularItemsData.colors,
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
                        ticks: { color: '#6b5c52', stepSize: 5 },
                        suggestedMax: 35
                    }
                }
            }
        });
    }
</script>
</body>
</html>
