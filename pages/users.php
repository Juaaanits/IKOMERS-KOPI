<?php
require_once '../includes/require_admin.php';
require_once '../includes/db.php';

$username = $_SESSION['username'] ?? 'User';
$initial = strtoupper(substr($username, 0, 1));
$users = [];

$dbReady = $conn && $conn instanceof mysqli && $conn->connect_errno === 0;
if ($dbReady) {
    // Ensure users table has all fields used by admin user management.
    $conn->query(
        "ALTER TABLE users
            ADD COLUMN IF NOT EXISTS full_name VARCHAR(120) NULL AFTER username,
            ADD COLUMN IF NOT EXISTS email VARCHAR(190) NULL AFTER full_name,
            ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL AFTER email,
            ADD COLUMN IF NOT EXISTS role VARCHAR(30) NOT NULL DEFAULT 'User' AFTER phone"
    );

    $conn->query(
        "CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users (email)"
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = trim($_POST['role'] ?? 'User');
        $allowedRoles = ['Admin', 'User'];

        if ($fullname !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '' && in_array($role, $allowedRoles, true)) {
            $emailLower = strtolower($email);
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, full_name, email, password, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
            if ($insert) {
                // Use email as username so login can work with either username or email.
                $insert->bind_param('ssssss', $emailLower, $fullname, $emailLower, $passwordHash, $phone, $role);
                $insert->execute();
                $insert->close();
            }
        }

        header('Location: users.php');
        exit;
    }

    $usersResult = $conn->query("SELECT id, username, full_name, email, password, phone, role FROM users ORDER BY id ASC");
    if ($usersResult) {
        while ($row = $usersResult->fetch_assoc()) {
            $users[] = [
                'id' => (int) $row['id'],
                'name' => $row['full_name'] ?: $row['username'],
                'email' => $row['email'] ?: $row['username'],
                'password' => $row['password'],
                'phone' => $row['phone'],
                'role' => $row['role']
            ];
        }
        $usersResult->free();
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
    <title>Users | IKOMERS KOPI</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/dashboard.css'); ?>">
    <link rel="stylesheet" href="../assets/css/users.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/users.css'); ?>">
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
                    <a href="users.php" aria-current="page">
                        <span class="nav-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 21V19C16 17.8954 15.1046 17 14 17H10C8.89543 17 8 17.8954 8 19V21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
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

        <section class="users-page" aria-label="User management">
            <div class="users-header">
                <div>
                    <h2>User Management</h2>
                </div>
            </div>

            <div class="users-table__wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fullname</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7">No users yet.</td>
                        </tr>
                    <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="password-mask">********</span>
                            </td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="actions-cell">
                                <button type="button" class="icon-btn icon-btn--edit js-edit-user" aria-label="Edit user" data-id="<?php echo (int) $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>" data-email="<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>" data-phone="<?php echo htmlspecialchars($user['phone'], ENT_QUOTES); ?>" data-role="<?php echo htmlspecialchars($user['role'], ENT_QUOTES); ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 20H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M15.5 4.5L19.5 8.5L10 18H6V14L15.5 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button type="button" class="icon-btn icon-btn--delete js-delete-user" aria-label="Delete user" data-id="<?php echo (int) $user['id']; ?>">
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

            <button id="users-fab" class="users-fab" type="button" aria-label="Add new user">+</button>

            <dialog id="userModal" class="users-modal">
                <form class="users-modal__card" id="user-form" method="post" action="#" novalidate>
                    <input type="hidden" name="id" id="user-id" value="">
                    <header class="users-modal__header">
                        <h3 id="user-form-title">Add New User</h3>
                        <button type="button" class="close-btn" id="closeUserModal" aria-label="Close add user form">&times;</button>
                    </header>
                    <div class="form-grid">
                        <label class="field">
                            <span>Fullname</span>
                            <input type="text" name="fullname" id="user-fullname" placeholder="Fullname" required>
                        </label>
                        <label class="field">
                            <span>Email</span>
                            <input type="email" name="email" id="user-email" placeholder="Email" required>
                        </label>
                        <label class="field">
                            <span>Password</span>
                            <input type="password" name="password" id="user-password" placeholder="Password" required>
                        </label>
                        <label class="field">
                            <span>Phone</span>
                            <input type="tel" name="phone" id="user-phone" placeholder="Phone">
                        </label>
                        <label class="field">
                            <span>Role</span>
                            <select name="role" id="user-role">
                                <option>Admin</option>
                                <option>User</option>
                            </select>
                        </label>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn--ghost" id="cancelUserModal">Cancel</button>
                        <button type="submit" class="btn btn--primary" id="user-submit-btn" name="add_user" value="1">Add User</button>
                    </div>
                </form>
            </dialog>
        </section>
    </main>
</div>
<script src="../assets/js/notify.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/notify.js'); ?>"></script>
<script src="../assets/js/custom/users.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/custom/users.js'); ?>"></script>
<script src="../assets/js/account-menu.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/account-menu.js'); ?>"></script>
<script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>


