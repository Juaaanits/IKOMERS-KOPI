<?php
session_start();
require_once '../includes/db.php';

$loginResult = '';
$usernameValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {

    // Sanitize input
    $usernameValue = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Check empty input
    if ($usernameValue === '' || $password === '') {
        $loginResult = 'Enter both login and password to continue.';
    } else {
        // Initialize
        $authenticated = false;
        $userId = null;
        $username = '';

        // Check database connection
        if ($conn && $conn instanceof mysqli && $conn->connect_errno === 0) {
            $conn->query(
                "ALTER TABLE users
                    ADD COLUMN IF NOT EXISTS full_name VARCHAR(120) NULL AFTER username,
                    ADD COLUMN IF NOT EXISTS email VARCHAR(190) NULL AFTER full_name,
                    ADD COLUMN IF NOT EXISTS phone VARCHAR(40) NULL AFTER email,
                    ADD COLUMN IF NOT EXISTS role VARCHAR(30) NOT NULL DEFAULT 'User' AFTER phone"
            );

            // Prepare query to allow login via username or email
            $stmt = $conn->prepare('
                SELECT id, username, full_name, email, password, role
                FROM users
                WHERE username = ? OR email = ?
                LIMIT 1
            ');

            if ($stmt) {
                // Bind login value for both placeholders
                $stmt->bind_param('ss', $usernameValue, $usernameValue);
                $stmt->execute();

                // Fetch user
                $result = $stmt->get_result();
                $user = $result ? $result->fetch_assoc() : null;
                $stmt->close();

                // Check if user exists and password matches
                $passwordHash = (string) ($user['password'] ?? '');
                $passwordOk = $user ? password_verify($password, $passwordHash) : false;

                // Backward compatibility for legacy plain-text rows.
                if (!$passwordOk && $user && !str_starts_with($passwordHash, '$2y$') && hash_equals($passwordHash, $password)) {
                    $passwordOk = true;
                    $rehash = password_hash($password, PASSWORD_DEFAULT);
                    $rehashStmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                    if ($rehashStmt) {
                        $uid = (int) $user['id'];
                        $rehashStmt->bind_param('si', $rehash, $uid);
                        $rehashStmt->execute();
                        $rehashStmt->close();
                    }
                }

                if ($user && $passwordOk) {

                    // Restrict to Admin role
                    $userRole = trim((string)($user['role'] ?? 'User'));
                    if (strcasecmp($userRole, 'Admin') !== 0) {
                        $loginResult = 'Only administrator access is allowed.';
                    } else {
                        // Admin authenticated
                        $authenticated = true;
                        $userId = (int)$user['id'];
                        $username = trim((string) ($user['full_name'] ?? '')) !== ''
                            ? (string) $user['full_name']
                            : (string) $user['username'];

                        // Set session securely
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['username'] = $username;
                        $_SESSION['is_admin'] = true;

                        header('Location: dashboard.php');
                        exit();
                    }

                } else {
                    $loginResult = 'Invalid login credentials.';
                }

            } else {
                $loginResult = 'Unable to run login query.';
            }

        } else {
            $loginResult = 'Database connection failure.';
        }
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
    <title>Administrator Login | IKOMERS KOPI</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
<main>
    <section class="auth-panel">
        <div class="panel-copy">
            <span class="eyebrow">Secure Access</span>
            <h1>Administrator Console</h1>
            <p>Sign in to monitor orders, manage the menu, and keep the coffee bar running smoothly.</p>
        </div>
        <div class="container">
            <h2>Admin Sign in</h2>
            <form method="post" autocomplete="on">
                <div class="input-details">
                    <label for="username" class="visually-hidden">Administrator Username</label>
                    <input type="text" id="login" name="login" placeholder="Username or email"
                           value="<?php echo htmlspecialchars($usernameValue ?? '', ENT_QUOTES); ?>" required>
                </div>
                <div class="input-details">
                    <label for="password" class="visually-hidden">Administrator Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <button
                            type="button"
                            id="togglePassword"
                            class="eye-btn password-toggle"
                            data-password-toggle="password"
                            aria-label="Show password"
                            aria-pressed="false"
                        >
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="remember-details">
                    <label><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                    <a href="#">Need help?</a>
                </div>
                <button type="submit" class="btn" name="login-btn">Enter Dashboard</button>
                <?php if ($loginResult !== ''): ?>
                    <div class="feedback-error"><?php echo htmlspecialchars($loginResult); ?></div>
                <?php endif; ?>
            </form>
            <div class="register-link">
                <p>Only administrator login is enabled for this system.</p>
            </div>
        </div>
    </section>
</main>
<script src="../assets/js/login.js"></script>
</body>
</html>
