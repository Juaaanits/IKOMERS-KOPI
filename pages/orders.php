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




</body>

</html>