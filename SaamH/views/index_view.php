<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= h((string) cfg('app_name')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap wrap-dashboard">
    <?php require __DIR__ . '/partials/nav.php'; ?>

    <?php if ($flash): ?>
        <div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-ok' ?>">
            <?= h((string) $flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="grid">
        <div>
			<?php require __DIR__ . '/partials/add_company_panel.php'; ?><br />
			<?php require __DIR__ . '/partials/todo_companies_panel.php'; ?>
		</div>
		<div>
			<?php require __DIR__ . '/partials/search_companies_panel.php'; ?>
			<?php require __DIR__ . '/partials/company_details_panel.php'; ?>
		</div>
	</div>
</div>
</body>
</html>