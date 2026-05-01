<div class="top">
    <div>
        <h1 style="margin:0 0 6px 0;"><?= h((string) cfg('app_name')) ?></h1>
        <div class="muted">Search, add, sync, and classify your companies</div>
    </div>
    <div class="actions-bar">
        <a class="btn orange" href="index.php?action=sync_all" onclick="return confirm('Sync all companies now?')">Sync all companies</a>
        <a class="btn light" href="companies_list.php">Open all companies table</a>
    </div>
</div>