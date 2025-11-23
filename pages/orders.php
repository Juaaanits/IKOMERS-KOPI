<?php
session_start();





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/IKOMERS-KOPI/assets/css/orders.css">
</head>

<body>
    <main>
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
                            <input type="text" id="total-amount-field" name="total-amount" placeholder="Total Amount"
                                disabled=true>
                        </div>
                        <h3>Status</h3>
                        <select name="order-status" id="order-status">
                            <option value="pending">Pending</option>
                        </select>
                        <div id="cancel-add-container">
                            <button class="cancel-add-order" id="cancel-order" type="button">Cancel</button>
                            <button class="cancel-add-order" id="add-item-order" type="submit">Add Order</button>
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
                    <div class="order-item">
                        <tr>
                            <td>131</td>
                            <td>Maria Sanchez</td>
                            <td>1x Cappuccino ($4.75)</td>
                            <td>4.75</td>
                            <td>
                                <div id="item-status">Completed</div>
                            </td>
                            <td>2025-01-05</td>
                            <td>
                                <button class="action-button" type="button">
                                    <img src="../assets/icons/overview.png" alt="view-order" class="img-action-button">
                                </button>
                                <button class="action-button" type="button">
                                    <img src="../assets/icons/file-edit.png" alt="edit-order" class="img-action-button">
                                </button>
                                <button class="action-button" type="button">
                                    <img src="../assets/icons/octagon-xmark.png" alt="remove-order"
                                        class="img-action-button">
                                </button>

                            </td>
                        </tr>
                    </div>
                </tbody>
            </table>
        </div>


    </main>

    <script src="../assets/js/custom/orders.js"></script>
</body>

</html>