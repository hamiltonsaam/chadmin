<header class="dashboard-header">
    <div class="header-content">
        <a href="<?= h((string) cfg('base_url')) ?>/index.php" class="app-title"><?= h((string) cfg('app_name')) ?></a>
        <nav>
            <?php if (is_logged_in()): ?>
                <?php $currentUser = get_logged_in_user(); ?>
                <span>Welcome, <?= h($currentUser['email'] ?? 'Admin') ?></span>
                <a href="index.php">User Management</a>
                <a href="companies.php">Master Companies List</a>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>