<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In — <?= h((string) cfg('app_name')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
  <link rel="stylesheet" href="views/theme/assets/css/styles.css" />
  <style>
    body.login-page { display: block !important; overflow: hidden; }
  </style>
</head>
<body class="login-page">

<div class="login-wrap">

  <!-- ── Left: form ───────────────────────────────── -->
  <div class="login-left">
    <div class="login-box">

      <!-- Logo -->
      <div class="login-logo">
        <div class="brand-icon">AAA</div>
        <span class="login-brand">AAA WEB-FILING</span>
      </div>

      <h1 class="login-title">Sign in</h1>
      <p class="login-sub">Access your Companies House dashboard</p>

      <!-- Flash message -->
      <?php if ($flash): ?>
      <div style="
        background:<?= $flash['type'] === 'error' ? '#ffdad6' : '#e6f4ea' ?>;
        color:<?= $flash['type'] === 'error' ? '#93000a' : '#137333' ?>;
        border:1px solid <?= $flash['type'] === 'error' ? '#ffb4ab' : '#ceead6' ?>;
        border-radius:var(--radius-md);
        padding:var(--space-3) var(--space-4);
        font-size:var(--text-body-sm);
        font-weight:600;
        margin-bottom:var(--space-4);
        display:flex;
        align-items:center;
        gap:var(--space-2);
      ">
        <span class="material-symbols-outlined" style="font-size:18px;">
          <?= $flash['type'] === 'error' ? 'error' : 'check_circle' ?>
        </span>
        <?= h((string) $flash['message']) ?>
      </div>
      <?php endif; ?>

      <!-- Error -->
      <?php if ($error): ?>
      <div style="
        background:#ffdad6;
        color:#93000a;
        border:1px solid #ffb4ab;
        border-radius:var(--radius-md);
        padding:var(--space-3) var(--space-4);
        font-size:var(--text-body-sm);
        font-weight:600;
        margin-bottom:var(--space-4);
        display:flex;
        align-items:center;
        gap:var(--space-2);
      ">
        <span class="material-symbols-outlined" style="font-size:18px;">error</span>
        <?= h($error) ?>
      </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="post" action="login.php" class="login-form">

        <div class="form-group">
          <label class="form-label" for="email">Email address</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-input"
            placeholder="you@example.com"
            required
            autofocus
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            class="form-input"
            placeholder="••••••••"
            required
          />
        </div>

        <button type="submit" class="btn-login">Sign in</button>

      </form>

      <p style="margin-top:var(--space-6);font-size:var(--text-body-sm);color:var(--on-surface-variant);text-align:center;">
        Don't have an account?
        <a href="register.php" style="color:var(--primary-container);font-weight:600;">Register here</a>
      </p>

    </div>
  </div>

  <!-- ── Right: branding panel ────────────────────── -->
  <div class="login-right">
    <p class="login-right-title">Manage your companies in one place</p>
    <p class="login-right-text">Track filings, deadlines, officers and confirmation statements — synced live from Companies House.</p>
  </div>

</div>

<script src="views/theme/assets/js/script.js" defer></script>
</body>
</html><!doctype html>
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