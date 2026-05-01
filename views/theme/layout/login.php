<?php
// Redirect if already logged in
// if (isset($_SESSION['user_id'])) {
//     header('Location: dashboard.php');
//     exit;
// }

// Handle form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId   = trim($_POST['user_id']   ?? '');
    $password = trim($_POST['password']  ?? '');

    // Replace this block with your real auth logic
    if (empty($userId) || empty($password)) {
        $error = 'Please enter your User ID and password.';
    } else {
        // Example: $user = authenticateUser($userId, $password);
        // if ($user) {
        //     $_SESSION['user_id'] = $user['id'];
        //     header('Location: dashboard.php');
        //     exit;
        // } else {
        //     $error = 'Invalid credentials. Please try again.';
        // }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In — AAA WEB-FILING</title>

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

  <!-- Stylesheet — adjust path if needed -->
  <link rel="stylesheet" href="/chadmin/views/theme/assets/css/styles.css" />
</head>
<body class="login-page">


<!-- ═══════════════════════════════════════
     LOGIN WRAPPER (left form + right brand)
═══════════════════════════════════════ -->
<div class="login-wrap">


  <!-- ── LEFT: Form Panel ── -->
  <div class="login-left">
    <div class="login-box">

      <!-- Logo -->
      <div class="login-logo">
        <div class="brand-icon">AAA</div>
        <span class="login-brand">AAA WEB-FILING</span>
      </div>

      <!-- Heading -->
      <h1 class="login-title">Sign in</h1>
      <p class="login-sub">Enter your credentials to access the portal.</p>

      <!-- Error message (shown only when $error is set) -->
      <?php if ($error): ?>
      <div style="
        background: var(--error-container);
        color: var(--on-error-container);
        border: 1px solid rgba(186,26,26,0.25);
        border-radius: var(--radius-md);
        padding: var(--space-3) var(--space-4);
        font-size: var(--text-body-sm);
        font-weight: 500;
        margin-bottom: var(--space-4);
        display: flex;
        align-items: center;
        gap: var(--space-2);
      ">
        <span class="material-symbols-outlined" style="font-size:18px;">error</span>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" action="login.php" class="login-form" novalidate>

        <!-- CSRF token — uncomment when you have a token function -->
        <!-- <input type="hidden" name="csrf_token" value="<?php /* echo generateCsrfToken(); */ ?>"> -->

        <div class="form-group">
          <label for="user_id" class="form-label">User ID</label>
          <div class="input-wrap">
            <span class="material-symbols-outlined input-icon">badge</span>
            <input
              type="text"
              id="user_id"
              name="user_id"
              class="form-input input-padded"
              placeholder="e.g. CH-99210"
              value="<?= htmlspecialchars($_POST['user_id'] ?? '') ?>"
              autocomplete="username"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <div class="input-wrap">
            <span class="material-symbols-outlined input-icon">lock</span>
            <input
              type="password"
              id="password"
              name="password"
              class="form-input input-padded"
              placeholder="Enter your password"
              autocomplete="current-password"
              required
            />
          </div>
        </div>

        <!-- Remember me + Forgot password row -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-top: calc(-1 * var(--space-2));">
          <label style="display:flex; align-items:center; gap: var(--space-2); font-size: var(--text-body-sm); color: var(--on-surface-variant); cursor:pointer;">
            <input type="checkbox" name="remember" id="remember" style="accent-color: var(--primary-container); width:15px; height:15px;" />
            Remember me
          </label>
          <a href="forgot-password.php" class="btn-ghost" style="font-size: var(--text-body-sm);">
            Forgot password?
          </a>
        </div>

        <button type="submit" class="btn-login">
          Sign in
          <span class="material-symbols-outlined" style="font-size:18px; vertical-align:middle; margin-left: var(--space-1);">arrow_forward</span>
        </button>

      </form>

      <!-- Footer note -->
      <p style="margin-top: var(--space-8); font-size: var(--text-body-sm); color: var(--on-surface-variant); text-align:center;">
        Having trouble?
        <a href="mailto:support@hamiltonn.net" class="btn-ghost" style="font-size: var(--text-body-sm);">
          Contact support
        </a>
      </p>

    </div>
  </div><!-- /.login-left -->


  <!-- ── RIGHT: Brand Panel ── -->
  <div class="login-right">

    <!-- Icon -->
    <span class="material-symbols-outlined" style="font-size:64px; opacity:0.6; margin-bottom: var(--space-6);">
      verified_user
    </span>

    <!-- Heading -->
    <h2 class="login-right-title">Companies House<br>Compliance Portal</h2>

    <!-- Subtext -->
    <p class="login-right-text">
      Manage filings, track deadlines, and maintain compliance across your entire company portfolio — all in one place.
    </p>

    <!-- Feature bullets -->
    <ul style="list-style:none; margin-top: var(--space-8); display:flex; flex-direction:column; gap: var(--space-4); max-width:320px; width:100%;">
      <li style="display:flex; align-items:center; gap: var(--space-3); font-size: var(--text-body-sm); opacity:0.9;">
        <span class="material-symbols-outlined" style="font-size:20px; flex-shrink:0;">check_circle</span>
        Real-time filing status &amp; deadline tracking
      </li>
      <li style="display:flex; align-items:center; gap: var(--space-3); font-size: var(--text-body-sm); opacity:0.9;">
        <span class="material-symbols-outlined" style="font-size:20px; flex-shrink:0;">check_circle</span>
        Manage multiple companies in one portfolio
      </li>
      <li style="display:flex; align-items:center; gap: var(--space-3); font-size: var(--text-body-sm); opacity:0.9;">
        <span class="material-symbols-outlined" style="font-size:20px; flex-shrink:0;">check_circle</span>
        Secure role-based access for your team
      </li>
    </ul>

  </div><!-- /.login-right -->

</div><!-- /.login-wrap -->


<!-- Script (only theme toggle is needed here, sidebar auto-active is irrelevant) -->
<script src="/chadmin/views/theme/assets/js/script.js" defer></script>

</body>
</html>