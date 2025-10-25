<?php
session_start();
require_once '../includes/db.php';

$loginResult = '';
$usernameValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login-btn'])) {
    $usernameValue = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($usernameValue === '' || $password === '') {
        $loginResult = 'Enter both username and password to continue.';
    } else {
        $authenticated = false;
        $userId = null;
        $username = null;

        if ($conn && $conn instanceof mysqli && $conn->connect_errno === 0) {
            $stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $usernameValue);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows === 1) {
                    $stmt->bind_result($userId, $dbUsername, $dbPassword);
                    $stmt->fetch();

                    if (password_verify($password, $dbPassword) || $password === $dbPassword) {
                        $authenticated = true;
                        $username = $dbUsername;
                    }
                } else {
                    $loginResult = 'Account not found.';
                }

                $stmt->close();
            } else {
                $loginResult = 'Unable to run login query.';
            }
        } else {
            $loginResult = 'Database connection failure.';
        }

        if ($authenticated) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;

            header('Location: dashboard.php');
            exit();
        } else {
            $loginResult = $loginResult ?: 'The credentials you entered did not match our records.';
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
                    <input type="text" id="username" name="username" placeholder="Admin username"
                           value="<?php echo htmlspecialchars($usernameValue, ENT_QUOTES); ?>" required>
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
                <p>Looking for the guest portal? <a href="login.php">Switch to guest login</a></p>
            </div>
        </div>
    </section>
</main>
<script src="../assets/js/login.js"></script>
</body>
</html>
