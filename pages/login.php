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
                <form name="login" action="" method="post" autocomplete="on">
                    <div class="input-details">
                        <label for="username" class="visually-hidden">Username</label>
                        <input type="text" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="input-details">
                        <label for="password" class="visually-hidden">Password</label>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="remember-details">
                        <label>
                            <input type="checkbox" name="remember" value="1">
                            Remember me
                        </label>
                        <a href="#">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn" name="login-btn">Login</button>
                    <?php if (!empty($loginResult)) { ?>
                        <div class="feedback-error"><?php echo $loginResult; ?></div>
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
