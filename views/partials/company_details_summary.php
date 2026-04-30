<?php
$activeOfficers = decode_json_list($selectedCompany['active_officers_json'] ?? null);
$activePscs = decode_json_list($selectedCompany['active_pscs_json'] ?? null);
$statusClass = status_badge_class($companyStatus);
?>
<div class="top">
    <div>
        <h2 style="margin:0 0 6px 0;"><?= h(company_display_name_with_label($selectedCompany)) ?></h2>
        <div class="muted"><?= h($selectedCompany['company_number']) ?></div>
    </div>
    <div class="actions-bar">
		<a class="btn gray" href="index.php?action=archive&company=<?= urlencode($selectedCompany['company_number']) ?>" onclick="return confirm('Archive this company?')">Archive</a>
		<a class="btn red" href="index.php?action=delete&company=<?= urlencode($selectedCompany['company_number']) ?>" onclick="return confirm('Are you sure you want to delete it?');">Delete</a>
		<a class="btn" href="filing_page.php?company=<?= urlencode($selectedCompany['company_number']) ?>" target="_blank" rel="noopener noreferrer">Web filing</a>
		<a class="btn" href="software_filing_test.php?company=<?= urlencode($selectedCompany['company_number']) ?>" target="_blank" rel="noopener noreferrer">Software filing test</a>
		<a class="btn light" href="<?= h(companies_house_company_url($selectedCompany['company_number'])) ?>" target="_blank" rel="noopener noreferrer">Open on Companies House</a>
	</div>
</div>

<form method="post" action="index.php?action=update_category" style="margin-top:16px;">
    <input type="hidden" name="company_number" value="<?= h($selectedCompany['company_number']) ?>">
    <div class="row">
        <label>
            Existing category
            <select name="category_existing">
                <option value="">Select category</option>
                <?php foreach ($categories as $categoryItem): ?>
                    <option value="<?= h($categoryItem) ?>" <?= ($selectedCompany['category'] ?? '') === $categoryItem ? 'selected' : '' ?>><?= h($categoryItem) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Or new category
            <input type="text" name="category_new" placeholder="New category">
        </label>
    </div>
    <button type="submit">Save category</button>
</form>

<div style="margin-top:16px;">
    <a class="btn" href="index.php?action=sync&company=<?= urlencode($selectedCompany['company_number']) ?>">Sync now</a>
</div>