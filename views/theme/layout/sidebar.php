<?php
$userId  = $_SESSION['user_id']  ?? null;
$isAdmin = !empty($_SESSION['is_admin']);
?>
<aside class="sidebar" id="mainSidebar" aria-label="Main navigation">

  <!-- Close button (mobile only) -->
  <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close navigation menu">
    <span class="material-symbols-outlined">close</span>
  </button>

  <div class="sidebar-top">

    <div class="brand">
      <div class="brand-logo">
        <div class="brand-icon">A</div>
        <span class="brand-name">A1A eFiling</span>
      </div>
      <span class="brand-subtitle">Compliance Portal</span>
    </div>

    <?php if ($isAdmin): ?>
      <a href="index.php?add=1" class="btn-new-filing">
        <span class="material-symbols-outlined">add</span>
        Add Company
      </a>
    <?php endif; ?>

  </div>

  <ul class="sidebar-menu">

    <li>
      <a href="index.php" class="sidebar-link">
        <span class="material-symbols-outlined">dashboard</span>
        Dashboard
      </a>
    </li>

    <li>
      <a href="companies_list.php" class="sidebar-link">
        <span class="material-symbols-outlined">domain</span>
        All Companies
      </a>
    </li>

    <li>
      <a href="deadlines.php" class="sidebar-link">
        <span class="material-symbols-outlined">event</span>
        Deadlines
      </a>
    </li>

    <?php if ($isAdmin): ?>
    <li>
      <a href="admin.php" class="sidebar-link">
        <span class="material-symbols-outlined">admin_panel_settings</span>
        Admin
      </a>
    </li>
    <?php endif; ?>

  </ul>

  <div class="sidebar-footer">
    <a href="profile.php" class="sidebar-link">
      <span class="material-symbols-outlined">manage_accounts</span>
      Profile
    </a>
    <a href="logout.php" class="sidebar-link">
      <span class="material-symbols-outlined">logout</span>
      Sign out
    </a>
  </div>

</aside>
