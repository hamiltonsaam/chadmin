<?php
require_login();

// ── Session user vars ───────────────────────────────────
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
<div class="topbar" role="banner">

  <!-- Left: Title + nav -->
  <div class="topbar-left">
    <span class="topbar-title">AAA WEB-FILING</span>

    <nav class="topbar-nav" aria-label="Top navigation">
      <a href="index.php">Dashboard</a>
      <span class="sep" aria-hidden="true">|</span>
      <a href="companies_list.php">All Companies</a>
      
    </nav>
  </div>

  <!-- Centre: Search (hidden on mobile, shown via CSS at 769px+) -->
		<form class="topbar-search" role="search" method="get" action="companies_list.php">
		  <span class="material-symbols-outlined" aria-hidden="true">search</span>

		  <input
			type="search"
			name="search"
			value="<?= h((string) ($_GET['search'] ?? '')) ?>"
			placeholder="Search companies…"
			aria-label="Search companies"
			autocomplete="off"
		  />

		  <button type="submit" class="sr-only">Search</button>
		</form>

  <!-- Right: Theme toggle + User ID -->
  <div class="topbar-right">

    <span class="user-id" aria-label="Signed in as <?= htmlspecialchars($userId) ?>">
      <strong>Welcome Back <?= htmlspecialchars($userDisplay) ?>!</strong>
    </span>

    <button
      id="themeToggle"
      class="icon-btn"
      aria-label="Switch to dark mode"
      title="Toggle dark mode"
    >
      <span class="material-symbols-outlined">dark_mode</span>
    </button>

        <a href="logout.php" class="icon-btn" title="Sign out">
        <span class="material-symbols-outlined">logout</span>
      </a>
  </div>

</div>