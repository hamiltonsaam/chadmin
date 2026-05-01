<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register - <?= h((string) cfg('app_name')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap wrap-login">
    <h1>Register</h1>

    <?php if ($error): ?>
        <div class="flash-error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" action="register.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        <button type="submit" class="btn">Register</button>
    </form>
    <p>
        Already have an account? <a href="login.php">Login here</a>.
    </p>
</div>
</body>
</html>