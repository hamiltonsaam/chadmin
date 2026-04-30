<?php
declare(strict_types=1);

require __DIR__ . '/../../bootstrap.php';

$flash        = get_flash();
$category     = trim((string) ($_GET['category'] ?? ''));
$sort         = trim((string) ($_GET['sort']     ?? 'name'));
$dir          = trim((string) ($_GET['dir']      ?? 'asc'));
$archivedOnly = (string) ($_GET['archived'] ?? '0') === '1';

$companies  = get_companies_for_table($category !== '' ? $category : null, $sort, $dir, $archivedOnly);
$categories = get_categories();

// ── Badge map ─────────────────────────────────────────────────
$badgeMap = [
    'Active'      => 'badge-active',
    'Dissolved'   => 'badge-dissolved',
    'Liquidation' => 'badge-liquidation',
    'Dormant'     => 'badge-dormant',
];

function sort_link(string $label, string $key, string $currentSort, string $currentDir, string $currentCategory, bool $archivedOnly): string
{
    $nextDir = ($currentSort === $key && strtolower($currentDir) === 'asc') ? 'desc' : 'asc';
    $params  = [
        'category' => $currentCategory,
        'sort'     => $key,
        'dir'      => $nextDir,
        'archived' => $archivedOnly ? '1' : '0',
    ];
    return '<a href="companies_list.php?' . h(http_build_query($params)) . '">' . h($label) . '</a>';
}

function date_class(?string $date): string
{
    return match (due_level($date)) {
        'orange' => 'date-yellow',
        'red'    => 'date-red',
        default  => '',
    };
}

// ── Pagination ────────────────────────────────────────────────
$search      = '';
$filterStatus = '';
$perPage     = 10;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalCount  = count($companies);
$totalPages  = max(1, (int) ceil($totalCount / $perPage));
$currentPage = min($currentPage, $totalPages);
$startRow    = ($currentPage - 1) * $perPage + 1;
$endRow      = min($currentPage * $perPage, $totalCount);
$pageRows    = array_slice($companies, ($currentPage - 1) * $perPage, $perPage);
?>

<?php if ($flash): ?>
<div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-ok' ?>" role="alert">
    <?= htmlspecialchars((string) $flash['message']) ?>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════
     COMPANIES TABLE
═══════════════════════════════════════ -->
<div class="table-container">

    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Company No.</th>
                    <th>Company Name</th>
                    <th class="th-center">Status</th>
                    <th class="th-right">Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($pageRows)): ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:var(--space-12); color:var(--on-surface-variant);">
                        <span class="material-symbols-outlined"
                              style="font-size:36px; display:block; margin:0 auto var(--space-3);">
                            domain_disabled
                        </span>
                        No companies match your search.
                    </td>
                </tr>

            <?php else: ?>
            <?php foreach ($pageRows as $i => $c):
                $status      = (string) ($c['company_status'] ?? '');
                $name        = (string) company_display_name_with_label($c);
                $number      = (string) ($c['company_number'] ?? '');
                $badge       = $badgeMap[$status] ?? 'badge-neutral';
                $isDissolved = in_array(strtolower($status), ['dissolved', 'liquidation']);
                $rowClass    = $i % 2 !== 0 ? 'row-alt' : '';

                // ── Worst-case due status for status badge colour ──
                $accountsRaw = (string) ($c['accounts_due_date']                   ?? '');
                $confRaw     = (string) ($c['confirmation_statement_due_date']      ?? '');
                $accountsDue = $accountsRaw !== '' ? due_status($accountsRaw) : null;
                $confDue     = $confRaw     !== '' ? due_status($confRaw)     : null;

                $worstStatus = 'ok';
                foreach ([$accountsDue, $confDue] as $d) {
                    if ($d === null) continue;
                    if ($d['status'] === 'overdue')  { $worstStatus = 'overdue'; break; }
                    if ($d['status'] === 'due_soon')   $worstStatus = 'due_soon';
                }

                if ($worstStatus === 'overdue') {
                    $statusBadgeBg    = '#ffdad6';
                    $statusBadgeColor = '#93000a';
                    $statusBadgeDot   = 'var(--error)';
                } elseif ($worstStatus === 'due_soon') {
                    $statusBadgeBg    = '#fff7ed';
                    $statusBadgeColor = '#b45309';
                    $statusBadgeDot   = '#d97706';
                } else {
                    $statusBadgeBg    = '#dcfce7';
                    $statusBadgeColor = '#166534';
                    $statusBadgeDot   = '#16a34a';
                }
            ?>
            <tr class="<?= $rowClass ?>">

                <!-- Company number -->
                <td class="td-mono td-nowrap">
                    <?= htmlspecialchars($number) ?>
                </td>

                <!-- Company name -->
                <td class="td-medium <?= $isDissolved ? 'td-strike' : '' ?>">
                    <?= htmlspecialchars($name) ?>
                </td>

                <!-- Status badge -->
                <td class="td-center">
                    <span class="badge" style="
                        background:<?= $statusBadgeBg ?>;
                        color:<?= $statusBadgeColor ?>;
                        display:inline-flex;
                        align-items:center;
                        gap:5px;
                    ">
                        <span style="
                            width:7px;height:7px;
                            border-radius:50%;
                            background:<?= $statusBadgeDot ?>;
                            flex-shrink:0;
                            display:inline-block;
                        "></span>
                        <?= htmlspecialchars($status) ?>
                    </span>
                </td>

                <!-- Actions -->
                <td class="td-right td-nowrap">
                    <?php if ($archivedOnly): ?>
                        <a href="companies_list.php?<?= http_build_query([
                            'action'   => 'unarchive',
                            'company'  => $number,
                            'category' => $category,
                            'sort'     => $sort,
                            'dir'      => $dir,
                            'archived' => '1',
                        ]) ?>"
                           class="btn btn-secondary btn-sm"
                           onclick="return confirm('Restore <?= htmlspecialchars(addslashes($name)) ?>?')">
                            <span class="material-symbols-outlined" style="font-size:14px;">unarchive</span>
                            Restore
                        </a>
                    <?php else: ?>
                        <a href="company_details.php?company=<?= urlencode($number) ?>"
                           class="btn btn-secondary btn-sm">
                            View
                            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
                        </a>
                    <?php endif; ?>
                </td>

            </tr>
            <?php endforeach; ?>
            <?php endif; ?>

            </tbody>
        </table>
    </div><!-- /.table-scroll -->


    <!-- ── Pagination ── -->
    <?php if ($totalCount > 0): ?>
    <div class="pagination">

        <!-- Info -->
        <span class="pagination-info">
            Showing <?= $startRow ?>–<?= $endRow ?> of <?= $totalCount ?> companies
        </span>

        <!-- Controls -->
        <div class="pagination-controls">

            <!-- Previous -->
            <?php if ($currentPage > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>"
               class="pg-btn" aria-label="Previous page">
                <span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
            </a>
            <?php else: ?>
            <button class="pg-btn" disabled aria-label="Previous page">
                <span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
            </button>
            <?php endif; ?>

            <!-- Page numbers -->
            <?php
            $range = 2;
            for ($p = 1; $p <= $totalPages; $p++):
                if ($p === 1 || $p === $totalPages || ($p >= $currentPage - $range && $p <= $currentPage + $range)):
            ?>
                <?php if ($p > 1 && $p === $currentPage - $range && $currentPage - $range > 2): ?>
                    <span class="pg-ellipsis">…</span>
                <?php endif; ?>

                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
                   class="pg-btn <?= $p === $currentPage ? 'active' : '' ?>"
                   aria-label="Page <?= $p ?>"
                   <?= $p === $currentPage ? 'aria-current="page"' : '' ?>>
                    <?= $p ?>
                </a>

                <?php if ($p < $totalPages && $p === $currentPage + $range && $currentPage + $range < $totalPages - 1): ?>
                    <span class="pg-ellipsis">…</span>
                <?php endif; ?>

            <?php endif; endfor; ?>

            <!-- Next -->
            <?php if ($currentPage < $totalPages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>"
               class="pg-btn" aria-label="Next page">
                <span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
            </a>
            <?php else: ?>
            <button class="pg-btn" disabled aria-label="Next page">
                <span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
            </button>
            <?php endif; ?>

        </div><!-- /.pagination-controls -->
    </div><!-- /.pagination -->
    <?php endif; ?>

</div><!-- /.table-container -->