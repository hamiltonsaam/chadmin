<?php
$userId  = $_SESSION['user_id'] ?? null;
$isAdmin = !empty($_SESSION['is_admin']);

$userDisplay = 'User';

if ($userId) {
    $stmt = db()->prepare("SELECT title, first_name FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $u = $stmt->fetch();

    if ($u) {
        $title = trim((string)($u['title'] ?? ''));
        $first = trim((string)($u['first_name'] ?? ''));

        $userDisplay = trim($title . ' ' . $first);

        if ($userDisplay === '') {
            $userDisplay = 'User';
        }
    }
}
?>
<aside class="sidebar" aria-label="Main navigation">

  <div class="sidebar-top">

    <!-- Brand -->
	<div class="brand-logo-image">   
	  <div class="brand-subtitle">
		<img src="/chadmin/views/theme/layout/logo.png" alt="AAA eFiling" class="brand-img">
		</div>
	</div>
 

    <!-- Sync All Companies -->
    <a href="index.php?action=sync_all" class="btn-new-filing">
      <span class="material-symbols-outlined">sync</span>
      Sync All
    </a>

    <!-- New Filing CTA ------ Coment out as not needed
    <a href="/chadmin/views/theme/layout/new-filing.php" class="btn-new-filing">
      <span class="material-symbols-outlined">add</span>
      New Filing
    </a>  -->

  </div><!-- /.sidebar-top -->


  <!-- ── Navigation: load based on role ── -->
	<?php if ($isAdmin): ?>
	  <?php include __DIR__ . '/nav/nav-admin.php'; ?>
	<?php else: ?>
	  <?php include __DIR__ . '/nav/nav-user.php'; ?>
	<?php endif; ?>


  <!-- ── Footer: settings + logout + user id ── -->
    <div class="sidebar-footer">
  <a class="sidebar-link" href="profile.php">
  <span class="material-symbols-outlined">account_circle</span>
  <span>Profile</span>
</a>
  
    <a href="/chadmin/views/theme/layout/settings.php" class="sidebar-link">
      <span class="material-symbols-outlined">settings</span>
      Settings
    </a>
    <a href="/chadmin/logout.php" class="sidebar-link">
      <span class="material-symbols-outlined">logout</span>
      Sign Out
    </a>
    <div style="padding: var(--space-3); font-size: var(--text-label-caps);
                color: var(--on-surface-variant); letter-spacing:0.04em;
                border-top: 1px solid var(--outline-variant); margin-top: var(--space-2);">
      Signed in as <strong><?= htmlspecialchars($userDisplay) ?></strong>
    </div>
  </div>

</aside>