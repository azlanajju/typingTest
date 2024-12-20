<?php  include("./processLogin.php");?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./login.css">
    <style>

        .error-message {
            color: #ff0000;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Enter your credentials to access your account</p>
        </div>
        <form method="post" >
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= $error_message ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox">
                    <span>Remember me</span>
                </label>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>
            <button type="submit" class="login-button">Log In</button>
            <div class="register-link">
                Don't have an account? <a href="../signup">Sign up</a>
            </div>
        </form>
    </div>
</body>
</html>
