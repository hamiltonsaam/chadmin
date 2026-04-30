<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Master Companies List - <?= h((string) cfg('app_name')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap wrap-dashboard">
    <?php require __DIR__ . '/nav.php'; ?>

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
        <div class="table-wrap">
            <table class="table-companies">
                <thead>
                    <tr>
                        <th style="width: 140px;">Company number</th>
                        <th>Company name</th>
                        <th>Owner (User)</th>
                        <th>Status</th>
                        <th>Last sync</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allCompanies)): ?>
                        <tr><td colspan="6" class="muted">No companies found in the database.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allCompanies as $company): ?>
                        <tr>
                            <td class="fw-700"><?= h($company['company_number']) ?></td>
                            <td>
                                <?= h($company['company_name'] ?: $company['label'] ?: '—') ?>
                                <?php if ($company['category']): ?>
                                    <br><span class="muted">Category: <?= h($company['category']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= h($company['owner_email'] ?: 'None (Orphaned)') ?></td>
                            <td><span class="badge badge-gray"><?= h($company['company_status'] ?: '—') ?></span></td>
                            <td class="muted"><?= h($company['last_synced_at'] ?: 'Never') ?></td>
                            <td>
                                <a href="companies.php?action=sync&company=<?= urlencode($company['company_number']) ?>">Sync</a>
                                | 
                                <a href="companies.php?action=delete&company=<?= urlencode($company['company_number']) ?>" onclick="return confirm('Are you sure you want to permanently delete this company?');" class="name-red">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>