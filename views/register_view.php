<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register — <?= h((string) cfg('app_name')) ?></title>
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

      <h1 class="login-title">Create account</h1>
      <p class="login-sub">Register to start managing your companies</p>

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
      <form method="post" action="register.php" class="login-form">

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
            placeholder="Min. 8 characters"
            required
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="password_confirm">Confirm password</label>
          <input
            type="password"
            id="password_confirm"
            name="password_confirm"
            class="form-input"
            placeholder="Repeat your password"
            required
          />
        </div>

        <button type="submit" class="btn-login">Create account</button>

      </form>

      <p style="margin-top:var(--space-6);font-size:var(--text-body-sm);color:var(--on-surface-variant);text-align:center;">
        Already have an account?
        <a href="login.php" style="color:var(--primary-container);font-weight:600;">Login here</a>
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
</html>