<?php
session_start();
require_once '../includes/db.php';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? ''; // Safe check
$loginResult = '';
$usernameValue = '';

// Only process POST requests
if ($requestMethod === 'POST' && isset($_POST['login-btn'])) {
    $usernameValue = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($usernameValue === '' || $password === '') {
        $loginResult = 'Please enter both username and password.';
    } else {
        $authenticated = false;
        $userId = null;
        $username = null;

        // Optional fallback user
        if ($usernameValue === 'juanito123' && $password === 'JuanitoRamos456') {
            $authenticated = true;
            $userId = 0;
            $username = 'juanito123';
        }

        // Check database if fallback not used
        if (!$authenticated && $conn && $conn instanceof mysqli && $conn->connect_errno === 0) {
            $stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $usernameValue);
                $stmt->execute();
                $stmt->bind_result($dbId, $dbUsername, $dbPassword);
                if ($stmt->fetch()) {
                    // Verify password
                    if (password_verify($password, $dbPassword)) {
                        $authenticated = true;
                        $userId = $dbId;
                        $username = $dbUsername;
                    }
                }
                $stmt->close();
            }
        }

        if ($authenticated) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            header('Location: placeholder.php');
            exit(); // Important: stop execution after redirect
        } else {
            $loginResult = 'Invalid username or password.';
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
    <title>Login | IKOMERS KOPI</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <main>
        <section class="auth-panel">
            <div class="panel-copy">
                <span class="eyebrow">Welcome back</span>
                <h1>Log in to IKOMERS KOPI</h1>
                <p>Keep track of your favourite brews, manage orders, and pick up right where you left off.</p>
            </div>
            <div class="container">
                <h2>Sign in</h2>
                <form method="post" autocomplete="on">
                    <div class="input-details">
                        <label for="username" class="visually-hidden">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username"
                               value="<?php echo htmlspecialchars($usernameValue, ENT_QUOTES); ?>" required>
                    </div>
                    <div class="input-details">
                        <label for="password" class="visually-hidden">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="remember-details">
                        <label><input type="checkbox" name="remember" value="1"> Remember me</label>
                        <a href="#">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn" name="login-btn">Login</button>
                    <?php if ($loginResult !== '') { ?>
                        <div class="feedback-error"><?php echo htmlspecialchars($loginResult); ?></div>
                    <?php } ?>
                </form>
                <div class="register-link">
                    <p>New here? <a href="signup.php">Create an account</a></p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
