<?php
function sort_link(string $label, string $key, string $currentSort, string $currentDir, string $currentCategory, bool $archivedOnly, ?int $filterUserId): string
{
    $nextDir = ($currentSort === $key && strtolower($currentDir) === 'asc') ? 'desc' : 'asc';

    $params = [
        'category' => $currentCategory,
        'sort' => $key,
        'dir' => $nextDir,
        'archived' => $archivedOnly ? '1' : '0',
    ];
    if ($filterUserId !== null) {
        $params['filter_user_id'] = $filterUserId;
    }
    $query = http_build_query($params);

    return '<a href="companies.php?' . h($query) . '">' . h($label) . '</a>';
}

function date_class(?string $date): string
{
    return match (due_level($date)) {
        'orange' => 'date-yellow',
        'red' => 'date-red',
        default => '',
    };
}
?>

<div class="table-wrap">
    <table class="table-companies">
        <thead>
        <tr>
            <th class="col-company-number">Company number</th>
            <th class="col-company-name"><?= sort_link('Company name', 'name', $sort, $dir, $category, $archivedOnly, $filterUserId) ?></th>
            <th class="col-owner"><?= sort_link('Owner', 'owner', $sort, $dir, $category, $archivedOnly, $filterUserId) ?></th>
            <th class="col-address">Registered address</th>
            <th class="col-status"><?= sort_link('Company status', 'status', $sort, $dir, $category, $archivedOnly, $filterUserId) ?></th>
            <th class="col-accounts"><?= sort_link('Date of account', 'accounts', $sort, $dir, $category, $archivedOnly, $filterUserId) ?></th>
            <th class="col-statement"><?= sort_link('Confirmation statement date', 'statement', $sort, $dir, $category, $archivedOnly, $filterUserId) ?></th>
            <th class="col-officers">Active directors and secretary</th>
            <th class="col-pscs">Active PSCs</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($companies as $company): ?>
            <?php
            $officers = decode_json_list($company['active_officers_json'] ?? null);
            $pscs = decode_json_list($company['active_pscs_json'] ?? null);
            $statusClass = status_badge_class($company['company_status'] ?? '');
            $filterParams = http_build_query(['category' => $category, 'sort' => $sort, 'dir' => $dir, 'archived' => $archivedOnly ? '1' : '0', 'filter_user_id' => $filterUserId]);
            ?>
            <tr>
                <td>
                    <a href="companies.php?action=view_company&company=<?= urlencode($company['company_number']) ?>" target="_blank">
                        <?= h($company['company_number']) ?>
                    </a>
                    <div class="mt-8 small-links">
                        <a href="companies.php?action=delete&company=<?= urlencode($company['company_number']) ?>&<?= $filterParams ?>" onclick="return confirm('Are you sure you want to delete it?');" class="name-red">Delete</a>
                    </div>
                    <?php if ($archivedOnly): ?>
                        <div class="mt-8">
                            <a class="btn gray" href="companies.php?action=unarchive&company=<?= urlencode($company['company_number']) ?>&archived=1&<?= $filterParams ?>">Unarchive</a>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
					<span class="dot <?= h(company_dot_class($company)) ?>"></span>
					<?= h(company_display_name_with_label($company)) ?>
				</td>
                <td class="email-text"><?= h($company['owner_email'] ?? 'Orphaned') ?></td>
                <td><?= h($company['registered_address'] ?: '') ?></td>
                <td><span class="badge <?= h($statusClass) ?>"><?= h($company['company_status'] ?: '—') ?></span></td>
                <td class="<?= h(date_class($company['accounts_due_date'] ?? null)) ?>">
                    <?= h(format_date_display($company['accounts_due_date'] ?? null)) ?>
                </td>
                <td class="<?= h(date_class($company['confirmation_statement_due_date'] ?? null)) ?>">
                    <?= h(format_date_display($company['confirmation_statement_due_date'] ?? null)) ?>
                </td>
                <td><?= h(join_names_for_table($officers, 'role')) ?></td>
                <td><?= h(join_names_for_table($pscs)) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>