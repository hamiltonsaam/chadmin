<div class="card">
    <h2 style="margin-top:0;">TODO</h2>

    <form method="get" action="index.php" style="margin-bottom:18px;">
        <input type="hidden" name="search_type" value="<?= h($searchType) ?>">
        <input type="hidden" name="q" value="<?= h($searchQuery) ?>">
        <input type="hidden" name="category" value="<?= h($defaultCategory) ?>">
        <?php if ($selectedCompanyNumber !== ''): ?>
            <input type="hidden" name="company" value="<?= h($selectedCompanyNumber) ?>">
        <?php endif; ?>

        <label>
            Filter by category
            <select name="todo_category">
                <option value="">All categories</option>
                <?php foreach ($categories as $categoryItem): ?>
                    <option value="<?= h($categoryItem) ?>" <?= $todoCategory === $categoryItem ? 'selected' : '' ?>>
                        <?= h($categoryItem) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit">Apply filter</button>
    </form>

    <?php if (!$todoCompanies): ?>
        <div class="muted">No TODO companies found.</div>
    <?php else: ?>
        <?php foreach ($todoCompanies as $company): ?>
            <?php
            $displayName = company_display_name_with_label($company);
            $nameColor = dashboard_company_color_hex($company);
            ?>
            <div class="company-item">
                <div style="font-weight:700; color: <?= h($nameColor) ?>;">
                    <span class="dot <?= h(company_dot_class($company)) ?>"></span>
					<?= h($displayName) ?>
                </div>
                <div class="muted"><?= h($company['company_number']) ?></div>
                <div class="muted">Category: <?= h($company['category'] ?: '—') ?></div>
                <div class="muted">Last sync: <?= h($company['last_synced_at'] ?: 'Never') ?></div>
                <div class="small-links" style="margin-top:10px;">
                <a href="index.php?company=<?= urlencode($company['company_number']) ?>&todo_category=<?= urlencode($todoCategory) ?>">Open</a>
                <a href="index.php?action=sync&company=<?= urlencode($company['company_number']) ?>">Sync</a>
                <a href="index.php?action=delete&company=<?= urlencode($company['company_number']) ?>" onclick="return confirm('Are you sure you want to delete it?');" class="name-red">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>