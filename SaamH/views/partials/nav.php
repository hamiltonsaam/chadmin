<header class="dashboard-header">
    <div class="header-content">
        <a href="<?= h((string) cfg('base_url')) ?>/index.php" class="app-title"><?= h((string) cfg('app_name')) ?></a>
        <nav>
            <?php if (is_logged_in()): ?>
                <?php $currentUser = get_logged_in_user(); ?>
                <span>Welcome, <?= h($currentUser['email'] ?? 'User') ?></span>
                <?php if (is_admin()): ?>
                    <a href="<?= h((string) cfg('base_url')) ?>/SaamH/users.php">Admin Dashboard</a>
                <?php endif; ?>
                <a href="<?= h((string) cfg('base_url')) ?>/companies_list.php">All Companies List</a>
                <a href="<?= h((string) cfg('base_url')) ?>/logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>