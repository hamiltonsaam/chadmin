<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - <?= h((string) cfg('app_name')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap wrap-login">
    <h1>Login</h1>

    <?php if ($flash): ?>
        <div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-ok' ?>">
            <?= h((string) $flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="flash-error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    <p>
        Don't have an account? <a href="register.php">Register here</a>.
    </p>
</div>
</body>
</html>