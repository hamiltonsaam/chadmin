<div class="top">
    <div>
        <h1 class="page-title">All companies</h1>
        <div class="muted"><?= $archivedOnly ? 'Archived companies' : 'Active companies' ?></div>
    </div>
    <div class="actions-bar">
        <?php if ($archivedOnly): ?>
            <a class="btn gray" href="companies_list.php">View active companies</a>
        <?php else: ?>
            <a class="btn gray" href="companies_list.php?archived=1">View archived companies</a>
        <?php endif; ?>
        <a class="btn" href="index.php">Back to dashboard</a>
    </div>
</div>