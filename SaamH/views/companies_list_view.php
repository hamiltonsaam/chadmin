<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>All companies</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
<div class="wrap wrap-dashboard">
    <?php require __DIR__ . '/../nav.php'; ?>

    <?php if ($flash): ?>
        <div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-ok' ?>">
            <?= h((string) $flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="top" style="margin-bottom: 20px;">
        <h1>Master Companies List</h1>
        <a href="companies.php?action=sync_all" class="btn green" onclick="return confirm('This will sync EVERY company in the database. It may take a while. Continue?');">Sync All Existing Companies</a>
    </div>

    <div class="card">
        <?php require __DIR__ . '/partials/companies_list_filters.php'; ?>
        <?php require __DIR__ . '/partials/companies_list_table.php'; ?>
    </div>
</div>
</body>
</html>