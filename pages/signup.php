<?php
session_start();
require_once '../includes/db.php';

$usernameValue = '';
$signupResult = '';
$signupResultType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup-btn'])) {
    $usernameValue = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($usernameValue === '' || $password === '' || $confirmPassword === '') {
        $signupResult = 'Please fill in all required fields.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $usernameValue)) {
        $signupResult = 'Username should be 3-20 characters and use only letters, numbers, or underscores.';
    } elseif (strlen($password) < 8) {
        $signupResult = 'Password should be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $signupResult = 'Passwords do not match. Please re-enter them.';
    } elseif (!$conn || !($conn instanceof mysqli) || $conn->connect_errno !== 0) {
        $signupResult = 'Database connection failed. Please try again later.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');

        if (!$stmt) {
            $signupResult = 'Unable to validate username at the moment.';
        } else {
            $stmt->bind_param('s', $usernameValue);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $signupResult = 'That username is already taken. Try a different one.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                if ($hashedPassword === false) {
                    $signupResult = 'Unable to secure your password. Please try again.';
                } else {
                    $insertStmt = $conn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');

                    if (!$insertStmt) {
                        $signupResult = 'Registration failed due to a database error.';
                    } else {
                        $insertStmt->bind_param('ss', $usernameValue, $hashedPassword);

                        if ($insertStmt->execute()) {
                            $signupResult = 'Account created successfully! You can now sign in.';
                            $signupResultType = 'success';
                            $usernameValue = '';
                        } else {
                            $signupResult = 'Could not create your account. Please try again.';
                        }

                        $insertStmt->close();
                    }
                }
            }

            $stmt->close();
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
    <title>Sign Up | IKOMERS KOPI</title>
    <link rel="stylesheet" href="../assets/css/signup.css">
</head>
<body>
<main>
    <div class="container">
        <h1>Create an account</h1>
        <p>Join IKOMERS KOPI to save your favourite blends, track orders, and enjoy members-only perks.</p>

        <?php if ($signupResult !== ''): ?>
            <div class="<?php echo $signupResultType === 'success' ? 'feedback-success' : 'feedback-error'; ?>">
                <?php echo htmlspecialchars($signupResult); ?>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="on">
            <div class="input-details">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="jane_doe"
                    value="<?php echo htmlspecialchars($usernameValue, ENT_QUOTES); ?>"
                    required
                >
            </div>

            <div class="input-details">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a password"
                        required
                    >
                    <button type="button" id="togglePassword" class="eye-btn" aria-label="Show password" aria-pressed="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="input-details">
                <label for="confirm_password">Confirm password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Re-type your password"
                    required
                >
            </div>

            <button type="submit" class="btn" name="signup-btn">Create account</button>
        </form>
        <div class="register-link">
            <p>Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </div>
</main>
<script src="../assets/js/script.js"></script>
</body>
</html>
