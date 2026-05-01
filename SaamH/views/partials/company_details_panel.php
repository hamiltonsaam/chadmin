<div class="card" style="margin-top:20px;">
    <?php if (!$selectedCompany): ?>
        <h2 style="margin-top:0;">Select a company</h2>
        <div class="muted">Add or search a company, then open it.</div>
    <?php else: ?>
        <?php require __DIR__ . '/company_details_summary.php'; ?>
        <?php require __DIR__ . '/company_details_status_grid.php'; ?>
        <?php require __DIR__ . '/company_details_people.php'; ?>
        <?php require __DIR__ . '/company_details_filings.php'; ?>
    <?php endif; ?>
</div>