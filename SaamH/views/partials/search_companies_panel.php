<div class="card">
    <h2 style="margin-top:0;">Search Companies House</h2>
    <form method="get" action="index.php">
        <div class="row">
            <label>
                Search type
                <select name="search_type">
                    <option value="company" <?= $searchType === 'company' ? 'selected' : '' ?>>Company name</option>
                    <option value="officer" <?= $searchType === 'officer' ? 'selected' : '' ?>>Director / officer name</option>
                </select>
            </label>
            <label>
                Search text
                <input type="text" name="q" value="<?= h($searchQuery) ?>" placeholder="Tesco or John Smith" required>
            </label>
        </div>
        <div class="row">
            <label>
                Category for added results
                <select name="category">
                    <option value="">Select category</option>
                    <?php foreach ($categories as $categoryItem): ?>
                        <option value="<?= h($categoryItem) ?>" <?= $defaultCategory === $categoryItem ? 'selected' : '' ?>><?= h($categoryItem) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <?php if ($todoCategory !== ''): ?>
            <input type="hidden" name="todo_category" value="<?= h($todoCategory) ?>">
        <?php endif; ?>
        <?php if ($selectedCompanyNumber !== ''): ?>
            <input type="hidden" name="company" value="<?= h($selectedCompanyNumber) ?>">
        <?php endif; ?>
        <button type="submit">Search</button>
    </form>

    <?php if ($searchQuery !== '' && $searchType === 'company'): ?>
        <div style="margin-top:20px;">
            <h3 style="margin-top:0;">Company results</h3>
            <?php if (!$companySearchResults): ?>
                <div class="muted">No results found.</div>
            <?php else: ?>
                <?php foreach ($companySearchResults as $item): ?>
                    <?php
                    $number = (string) ($item['company_number'] ?? '');
                    $name = (string) ($item['title'] ?? $item['company_name'] ?? $number);
                    $status = (string) ($item['company_status'] ?? '');
                    $address = (string) ($item['address_snippet'] ?? '');
                    ?>
                    <div class="result-box">
                        <div style="font-weight:700;"><?= h($name) ?></div>
                        <div class="muted">Company number: <?= h($number) ?></div>
                        <?php if ($status !== ''): ?><div class="muted">Status: <?= h($status) ?></div><?php endif; ?>
                        <?php if ($address !== ''): ?><div class="muted"><?= h($address) ?></div><?php endif; ?>
                        <div class="actions">
                            <a class="btn green" href="index.php?action=add_found_company&company_number=<?= urlencode($number) ?>&label=<?= urlencode($name) ?>&category=<?= urlencode($defaultCategory) ?>">Add company</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($searchQuery !== '' && $searchType === 'officer'): ?>
        <div style="margin-top:20px;">
            <h3 style="margin-top:0;">Director / officer results</h3>
            <?php if (!$officerSearchResultsWithCompanies): ?>
                <div class="muted">No results found.</div>
            <?php else: ?>
                <?php foreach ($officerSearchResultsWithCompanies as $row): ?>
                    <?php
                    $officer = $row['officer'];
                    $appointments = $row['appointments'];
                    $officerId = (string) ($row['officer_id'] ?? '');
                    $officerName = (string) ($officer['title'] ?? 'Officer');
                    $snippet = (string) ($officer['description'] ?? '');
                    ?>
                    <div class="result-box">
                        <div style="font-weight:700;"><?= h($officerName) ?></div>
                        <?php if ($snippet !== ''): ?>
                            <div class="muted"><?= h($snippet) ?></div>
                        <?php endif; ?>

                        <?php if ($officerId !== '' && $appointments): ?>
                            <div class="actions" style="margin-top:10px;">
                                <a class="btn green" href="index.php?action=add_all_officer_companies&appointments_path=<?= urlencode((string) ($row['appointments_path'] ?? '')) ?>&q=<?= urlencode($searchQuery) ?>&category=<?= urlencode($defaultCategory) ?>" onclick="return confirm('Add all companies for this officer?')">Add all companies</a>
                            </div>
                        <?php endif; ?>

                        <?php if (!$appointments): ?>
                            <div class="muted" style="margin-top:8px;">No active companies found for this officer.</div>
                        <?php else: ?>
                            <div style="margin-top:10px;">
                                <?php foreach ($appointments as $appointment): ?>
                                    <?php
                                    $companyNumber = (string) $appointment['company_number'];
                                    $companyName = (string) $appointment['company_name'];
                                    $companyStatusItem = (string) ($appointment['company_status'] ?? '');
                                    $officerRole = (string) ($appointment['officer_role'] ?? '');
                                    ?>
                                    <div class="result-box" style="margin-bottom:8px;">
                                        <div style="font-weight:700;"><?= h($companyName) ?></div>
                                        <div class="muted">Company number: <?= h($companyNumber) ?></div>
                                        <?php if ($companyStatusItem !== ''): ?>
                                            <div class="muted">Status: <?= h($companyStatusItem) ?></div>
                                        <?php endif; ?>
                                        <?php if ($officerRole !== ''): ?>
                                            <div class="muted">Role: <?= h($officerRole) ?></div>
                                        <?php endif; ?>
                                        <div class="actions">
                                            <a class="btn green" href="index.php?action=add_found_company&company_number=<?= urlencode($companyNumber) ?>&label=<?= urlencode($companyName) ?>&category=<?= urlencode($defaultCategory) ?>">Add company</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>