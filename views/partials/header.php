<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#003366" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#0f172a"  media="(prefers-color-scheme: dark)" />
  <meta name="mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="default" />
  <meta name="apple-mobile-web-app-title" content="<?= h((string) cfg('app_name')) ?>" />
  <title><?= h((string) cfg('app_name')) ?></title>
  <link rel="manifest" href="manifest.json" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
  <link rel="stylesheet" href="views/theme/assets/css/styles.css" />
</head>
<body>

<!-- Mobile sidebar backdrop overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

<aside class="sidebar" id="sidebar" aria-label="Main navigation">
  <div class="sidebar-top">
    <!-- Close button — visible on mobile only (CSS controls visibility) -->
    <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close menu">
      <span class="material-symbols-outlined">close</span>
    </button>
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
      <!-- Hamburger button — shown on mobile via CSS -->
      <button class="hamburger-btn" id="hamburgerBtn"
              aria-label="Open menu"
              aria-expanded="false"
              aria-controls="sidebar">
        <span class="material-symbols-outlined">menu</span>
      </button>
      <span class="topbar-title"><?= h((string) ($pageTitle ?? cfg('app_name'))) ?></span>
    </div>
    <!-- Desktop inline search (hidden on mobile via CSS) -->
    <div class="topbar-search" role="search">
      <span class="material-symbols-outlined">search</span>
      <input type="search" placeholder="Search…" aria-label="Search" />
    </div>
    <div class="topbar-right">
      <span class="user-id"><?= h((string) ($_SESSION['user_email'] ?? '')) ?></span>
      <button class="icon-btn" id="themeToggle" aria-label="Switch to dark mode">
        <span class="material-symbols-outlined">dark_mode</span>
      </button>
      <a href="logout.php" class="icon-btn" title="Sign out" aria-label="Sign out">
        <span class="material-symbols-outlined">logout</span>
      </a>
    </div>
  </header>

  <!-- Mobile search bar — shown below topbar on small screens only -->
  <div class="topbar-search-mobile" role="search" aria-label="Mobile search">
    <span class="material-symbols-outlined" aria-hidden="true">search</span>
    <input type="search" placeholder="Search companies…" aria-label="Search companies" />
  </div>

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
