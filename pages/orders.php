<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));

$sql = "SELECT * FROM ORDERS";
$orderResult = mysqli_query($conn, $sql);

$orderRows = mysqli_fetch_all($orderResult);

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
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 9.5L12 3L20 9.5V20H4V9.5Z" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M10 20V13H14V20" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <span class="nav-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 5H20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                    <path d="M4 12H20" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" />
                                    <path d="M4 19H20" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" />
                                </svg>
                            </span>
                            <span>Menu</span>
                        </a>
                    </li>
                    <li>
                        <a href="customers.php">
                            <span class="nav-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 21V19C20 16.7909 18.2091 15 16 15H8C5.79086 15 4 16.7909 4 19V21"
                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z"
                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span>Customers</span>
                        </a>
                    </li>
                    <li>
                        <a href="orders.php" aria-current="page">
                            <span class="nav-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 7H21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                    <path d="M7 11H17" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" />
                                    <path d="M5 15H19" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" />
                                    <path d="M9 19H15" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" />
                                </svg>
                            </span>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <span class="nav-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 21V19C16 17.8954 15.1046 17 14 17H10C8.89543 17 8 17.8954 8 19V21"
                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z"
                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M20 8V4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                    <path d="M22 6H18" stroke="currentColor" stroke-width="1.6"
                                        stroke-linecap="round" />
                                    <path d="M4 8V4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                                    <path d="M6 6H2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
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
                    <h1>Orders</h1>
                    <p>Café orders overview</p>
                </div>
                <div class="account-pill">
                    <span>Hello, <?php echo htmlspecialchars($username); ?></span>
                    <span class="account-avatar"><?php echo htmlspecialchars($initial); ?></span>
                </div>
            </header>

            <section class="orders-page" aria-label="Orders">
                <div id="header-container">
                    <header>
                        <h1>Cafe Orders</h1>
                    </header>
                    <button id="add-order" type="button">+</button>
                    <dialog id="order-form">
                        <form action="" method="POST">
                            <div id="dialog-order-form">
                                <header id="order-header-container">
                                    <h2>Add New Order</h2>
                                    <button id="close-order-form" type="button">
                                        <img src="../assets/icons/close-window.png" alt="close-order-form"
                                            id="close-order-form-button">
                                    </button>
                                </header>
                                <h3>Customer</h3>
                                <div id="customer-container-input">
                                    <input type="text" class="customer" id="customer-field" name="customer"
                                        placeholder="Customer Name" disabled=true>
                                    <div class="customer" id="id-number">
                                        <p>ID: 87</p>
                                    </div>
                                    <button class="customer" id="search-customer" type="button">
                                        <img src="../assets/icons/search-interface-symbol.png" alt="search-customer"
                                            id="search-customer-img">
                                    </button>
                                </div>
                                <h3>items</h3>
                                <button id="add-item" type="button">Add Item</button>
                                <table id="orders-customer-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <h4>Item</h4>
                                            </th>
                                            <th>
                                                <h4>ID</h4>
                                            </th>
                                            <th>
                                                <h4>Quantity</h4>
                                            </th>
                                            <th>
                                                <h4>Price</h4>
                                            </th>
                                            <th>
                                                <h4>Total</h4>
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                                <div id="total-amount-container">
                                    <h3>Total</h3>
                                    <input type="text" id="total-amount-field" name="total-amount"
                                        placeholder="Total Amount" disabled=true>
                                </div>
                                <h3>Status</h3>
                                <select name="order-status" id="order-status">
                                    <option value="pending">Pending</option>
                                </select>
                                <div id="cancel-add-container">
                                    <button class="cancel-add-order" id="cancel-order" type="button">Cancel</button>
                                    <button class="cancel-add-order" id="add-item-order" type="submit">Add
                                        Order</button>
                                </div>
                            </div>
                        </form>
                    </dialog>
                </div>
                <div id="filter-container">
                    <input type="text" id="search-field-order" name="searchOrder" placeholder="Search orders">
                    <select name="orderType" id="order-type">
                        <option value="all">All</option>
                    </select>
                </div>
                <div id="orders-container">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <h3>Order ID</h3>
                                </th>
                                <th>
                                    <h3>Customer</h3>
                                </th>
                                <th>
                                    <h3>Items</h3>
                                </th>
                                <th>
                                    <h3>Total</h3>
                                </th>
                                <th>
                                    <h3>Status</h3>
                                </th>
                                <th>
                                    <h3>Date</h3>
                                </th>
                                <th>
                                    <h3>Actions</h3>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orderNumRows = $orderResult->num_rows;

                            while ($orderNumRows !== 0) {
                                $row = $orderRows[$orderNumRows - 1];
                                $orderId = $row[0];
                                $item = $row[1];
                                $status = $row[3];
                                $dateTime = $row[4];
                                $customerId = $row[5];

                                $customerSql = "SELECT * FROM CUSTOMER WHERE CUSTOMER_ID = $customerId";
                                $customerResult = mysqli_query($conn, $customerSql);
                                $customerRow = mysqli_fetch_assoc($customerResult);
                                $customer = $customerRow['customer'];

                                ?>
                                <tr class="order-item">
                                    <td> <?php echo $orderId; ?> </td>
                                    <td> <?php echo $customer; ?> </td>
                                    <td> <?php echo $item; ?></td>
                                    <td>4.75</td>
                                    <td>
                                        <div id="item-status"> <?php echo $status; ?> </div>
                                    </td>
                                    <td> <?php echo $dateTime; ?> </td>
                                    <td>
                                        <button class="action-button" type="button">
                                            <img src="../assets/icons/overview.png" alt="view-order"
                                                class="img-action-button">
                                        </button>
                                        <button class="action-button" type="button">
                                            <img src="../assets/icons/file-edit.png" alt="edit-order"
                                                class="img-action-button">
                                        </button>
                                        <button class="action-button" type="button">
                                            <img src="../assets/icons/octagon-xmark.png" alt="remove-order"
                                                class="img-action-button">
                                        </button>
                                    </td>
                                </tr>
                                <?php
                                $orderNumRows--;
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="../assets/js/custom/orders.js"></script>
</body>

</html>
