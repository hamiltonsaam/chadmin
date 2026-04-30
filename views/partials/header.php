<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h((string) cfg('app_name')) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
  <link rel="stylesheet" href="views/theme/assets/css/styles.css" />
  <style>body { display:flex !important; overflow:hidden !important; }</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-top">
    <div class="brand">
      <div class="brand-logo">
        <div class="brand-icon">C</div>
        <span class="brand-name"><?= h((string) cfg('app_name')) ?></span>
      </div>
      <span class="brand-subtitle">AAA Webfiling Portal</span>
    </div>
    <a href="index.php?action=sync_all" class="btn-new-filing">
      <span class="material-symbols-outlined">sync</span>
      Sync All
    </a>
  </div>
  <ul class="sidebar-menu">
    <li>
      <a href="index.php" class="sidebar-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
        <span class="material-symbols-outlined">dashboard</span>
        Dashboard
      </a>
    </li>
    <li>
      <a href="companies_list.php" class="sidebar-link <?= ($activePage ?? '') === 'companies' ? 'active' : '' ?>">
        <span class="material-symbols-outlined">business</span>
        All Companies
      </a>
    </li>
    <li>
      <a href="filing_page.php" class="sidebar-link <?= ($activePage ?? '') === 'filings' ? 'active' : '' ?>">
        <span class="material-symbols-outlined">description</span>
        Filings
      </a>
    </li>
  </ul>
  <div class="sidebar-footer">
    <a href="logout.php" class="sidebar-link">
      <span class="material-symbols-outlined">logout</span>
      Sign out
    </a>
  </div>
</aside>

<div class="page">
  <header class="topbar">
    <div class="topbar-left">
      <span class="topbar-title"><?= h((string) ($pageTitle ?? cfg('app_name'))) ?></span>
    </div>
    <div class="topbar-right">
      <span class="user-id"><?= h((string) ($_SESSION['user_email'] ?? '')) ?></span>
      <button class="icon-btn" id="themeToggle" aria-label="Toggle dark mode">
        <span class="material-symbols-outlined">dark_mode</span>
      </button>
      <a href="logout.php" class="icon-btn" title="Sign out">
        <span class="material-symbols-outlined">logout</span>
      </a>
    </div>
  </header>

  <?php if (!empty($flash)): ?>
  <div style="
    margin:var(--space-4) var(--space-6) 0;
    padding:var(--space-3) var(--space-4);
    border-radius:var(--radius-md);
    font-size:var(--text-body-sm);
    font-weight:600;
    display:flex;
    align-items:center;
    gap:var(--space-2);
    <?= ($flash['type'] ?? '') === 'error'
      ? 'background:#ffdad6;color:#93000a;border:1px solid #ffb4ab;'
      : 'background:#e6f4ea;color:#137333;border:1px solid #ceead6;' ?>
  ">
    <span class="material-symbols-outlined" style="font-size:18px;">
      <?= ($flash['type'] ?? '') === 'error' ? 'error' : 'check_circle' ?>
    </span>
    <?= h((string) $flash['message']) ?>
  </div>
  <?php endif; ?>

  <main class="main-content"><div class="container">