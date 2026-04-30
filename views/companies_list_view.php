<?php
/**
 * views/companies_list_view.php
 *
 * Engine: companies_list.php (untouched)
 * Design: managed-companies.php template (exact match)
 *
 * Variables in scope from engine:
 *   array      $companies    – from get_companies_for_table()
 *   array      $categories   – from get_categories()
 *   string     $category     – active category filter
 *   string     $sort         – active sort column
 *   string     $dir          – 'asc' | 'desc'
 *   bool       $archivedOnly
 *   array|null $flash        – ['message'=>'…', 'type'=>'success'|'error']
 */

// ── Badge map (same as template) ─────────────────────────────
$activePage = 'all companies';
$pageTitle  = 'ALL COMPANIES';

$badgeMap = [
    'Active'      => 'badge-active',
    'Dissolved'   => 'badge-dissolved',
    'Liquidation' => 'badge-liquidation',
    'Dormant'     => 'badge-dormant',
];


function companies_sort_link(string $label, string $key, string $sort, string $dir): string
{
    $nextDir = ($sort === $key && $dir === 'asc') ? 'desc' : 'asc';
    $arrow = '';

    if ($sort === $key) {
        $arrow = $dir === 'asc' ? ' ↑' : ' ↓';
    }

    $params = array_merge($_GET, [
        'sort' => $key,
        'dir' => $nextDir,
        'page' => 1,
    ]);

    return '<a href="companies_list.php?' . htmlspecialchars(http_build_query($params), ENT_QUOTES, 'UTF-8') . '" style="color:inherit;text-decoration:none;font-weight:700;">'
        . htmlspecialchars($label . $arrow, ENT_QUOTES, 'UTF-8')
        . '</a>';
}
// ── Pagination (mirrors template logic) ──────────────────────
$search = trim((string) ($_GET['search'] ?? ''));
$filterStatus = trim((string) ($_GET['status'] ?? ''));
$perPage     = 10;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalCount  = count($companies);
$totalPages  = max(1, (int) ceil($totalCount / $perPage));
$currentPage = min($currentPage, $totalPages);
$startRow    = ($currentPage - 1) * $perPage + 1;
$endRow      = min($currentPage * $perPage, $totalCount);
$pageRows    = array_slice($companies, ($currentPage - 1) * $perPage, $perPage);

include __DIR__ . '/theme/layout/header.php';
?>

<?php if ($flash): ?>
<div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-ok' ?>" role="alert">
    <?= htmlspecialchars((string) $flash['message']) ?>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════ -->
<div class="page-header">
    <h1><?= $archivedOnly ? 'Archived Companies' : 'Managed Companies' ?></h1>
    <p><?= $archivedOnly
        ? 'Companies that have been archived. You can restore them at any time.'
        : 'View and manage your full portfolio of companies and their compliance status.'
    ?></p>
</div>


<!-- ═══════════════════════════════════════
     FILTER BAR
═══════════════════════════════════════ -->
<form method="GET" action="companies_list.php" class="filter-bar">

    <?php if ($archivedOnly): ?>
        <input type="hidden" name="archived" value="1">
    <?php endif; ?>
    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
    <input type="hidden" name="dir"  value="<?= htmlspecialchars($dir) ?>">

    <div class="filter-bar-controls">

        <!-- Search -->
        <div class="input-wrap" style="flex:1; min-width:200px;">
            <span class="material-symbols-outlined input-icon">search</span>
            <input
                type="text"
                name="search"
                class="input input-padded"
                placeholder="Search by name, number or address…"
                value="<?= htmlspecialchars((string) ($_GET['search'] ?? '')) ?>"
                autocomplete="off"
            />
        </div>

        <!-- Category filter -->
        <?php if (!empty($categories)): ?>
        <div class="select-wrap" style="min-width:160px;">
            <select name="category" class="select">
                <option value="">All categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"
                    <?= $category === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <span class="material-symbols-outlined select-arrow">expand_more</span>
        </div>
        <?php endif; ?>

        <!-- Status filter -->
        <div class="select-wrap" style="min-width:160px;">
            <select name="status" class="select">
                <option value="">All statuses</option>
                <?php foreach (['Active', 'Dissolved', 'Liquidation', 'Dormant'] as $s): ?>
                <option value="<?= $s ?>"
                    <?= (($_GET['status'] ?? '') === $s) ? 'selected' : '' ?>>
                    <?= $s ?>
                </option>
                <?php endforeach; ?>
            </select>
            <span class="material-symbols-outlined select-arrow">expand_more</span>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary">
            <span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>
            Filter
        </button>

        <!-- Clear -->
		<?php if ($category !== '' || $search !== '' || $filterStatus !== ''): ?>
        <a href="companies_list.php<?= $archivedOnly ? '?archived=1' : '' ?>"
           class="btn btn-secondary">
            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
            Clear
        </a>
        <?php endif; ?>

        <!-- Archived toggle -->
        <a href="companies_list.php<?= $archivedOnly ? '' : '?archived=1' ?>"
           class="btn btn-secondary" style="margin-left:auto;" data-no-row-click>
            <span class="material-symbols-outlined" style="font-size:18px;">
                <?= $archivedOnly ? 'unarchive' : 'archive' ?>
            </span>
            <?= $archivedOnly ? 'View Active' : 'View Archived' ?>
        </a>

    </div>
</form>


<!-- ═══════════════════════════════════════
     COMPANIES TABLE
═══════════════════════════════════════ -->
<div class="table-container">

    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= companies_sort_link('Company No.', 'number', $sort, $dir) ?></th>
					<th><?= companies_sort_link('Company Name', 'name', $sort, $dir) ?></th>
					<th class="th-address">Registered Address</th>
					<th class="th-center"><?= companies_sort_link('Status', 'status', $sort, $dir) ?></th>
					<th><?= companies_sort_link('Next Accounts', 'accounts_due', $sort, $dir) ?></th>
					<th><?= companies_sort_link('Conf. Statement', 'statement_due', $sort, $dir) ?></th>
					<th class="th-center">Officers</th>
					<th class="th-center">PSCs</th>
					<th class="th-right">Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($pageRows)): ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:var(--space-12); color:var(--on-surface-variant);">
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
				$address     = (string) ($c['registered_address'] ?? '');
				$badge       = $badgeMap[$status] ?? 'badge-neutral';
				$isDissolved = in_array(strtolower($status), ['dissolved', 'liquidation']);
				$rowClass    = $i % 2 !== 0 ? 'row-alt' : '';
				$officersList = decode_json_list($c['active_officers_json'] ?? null);
				$pscsList     = decode_json_list($c['active_pscs_json'] ?? null);

				// ── Due-status for both date columns (same logic as todo page) ──
				$accountsRaw  = (string) ($c['accounts_due_date'] ?? '');
				$confRaw      = (string) ($c['confirmation_statement_due_date'] ?? '');

				$accountsDue  = $accountsRaw  !== '' ? due_status($accountsRaw)  : null;
				$confDue      = $confRaw      !== '' ? due_status($confRaw)       : null;

				$accounts  = format_date_display($accountsRaw)  ?: '—';
				$conf_stmt = format_date_display($confRaw)       ?: '—';

				// Helper: resolve colour vars for a due result (mirrors todo page exactly)
				$resolveColors = function(?array $due): array {
					if ($due === null || $due['status'] === 'ok') {
						return ['cellColor' => '', 'tagBg' => '', 'tagColor' => '', 'tag' => ''];
					}
					if ($due['status'] === 'overdue') {
						return [
							'cellColor' => 'var(--error)',
							'tagBg'     => '#ffdad6',
							'tagColor'  => '#93000a',
							'tag'       => $due['tag'],
						];
					}
					// due_soon
					return [
						'cellColor' => '#b45309',
						'tagBg'     => '#fff7ed',
						'tagColor'  => '#b45309',
						'tag'       => $due['tag'],
					];
				};

				$aC = $resolveColors($accountsDue);
				$cC = $resolveColors($confDue);
				
				// ── Worst-case due status for the status badge ──
				$worstStatus = 'ok';
				foreach ([$accountsDue, $confDue] as $d) {
					if ($d === null) continue;
					if ($d['status'] === 'overdue') { $worstStatus = 'overdue'; break; }
					if ($d['status'] === 'due_soon') $worstStatus = 'due_soon';
				}

				// Map worst status → badge colours (mirrors todo page exactly)
				if ($worstStatus === 'overdue') {
					$statusBadgeBg    = '#ffdad6';
					$statusBadgeColor = '#93000a';
					$statusBadgeDot   = 'var(--error)';
				} elseif ($worstStatus === 'due_soon') {
					$statusBadgeBg    = '#fff7ed';
					$statusBadgeColor = '#b45309';
					$statusBadgeDot   = '#d97706';
				} else {
					// All clear — use your existing badge-active green
					$statusBadgeBg    = '#dcfce7';   // light green background
					$statusBadgeColor = '#166534';   // dark green text
					$statusBadgeDot   = '#16a34a';   // green dot
					}
			?>
            <tr   class="clickable-row"
			data-href="company_details.php?company=<?= urlencode($number) ?>">

                <!-- Company number -->
                <td class="td-mono td-nowrap">
					<a href="company_details.php?company=<?= urlencode($number) ?>" style="font-weight:700;text-decoration:none;">
						<?= htmlspecialchars($number) ?>
					</a>
				</td>

                <!-- Company name -->
                <td class="td-medium <?= $isDissolved ? 'td-strike' : '' ?>">
					<a href="company_details.php?company=<?= urlencode($number) ?>" style="font-weight:700;text-decoration:none;">
						<?= htmlspecialchars($name) ?>
					</a>
				</td>

                <!-- Address -->
                <td class="td-muted">
                    <?= htmlspecialchars($address) ?>
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

                <!-- Next Accounts -->
				<td class="td-nowrap <?= $accounts === '—' ? 'td-muted' : '' ?>">
					<?php if ($aC['cellColor'] !== ''): ?>
						<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
							<span style="width:8px;height:8px;border-radius:50%;
										 background:<?= $aC['cellColor'] ?>;
										 display:inline-block;flex-shrink:0;"></span>
							<span style="color:<?= $aC['cellColor'] ?>;font-weight:700;">
								<?= htmlspecialchars($accounts) ?>
							</span>
							<span style="font-size:10px;font-weight:600;padding:1px 6px;
										 border-radius:3px;
										 background:<?= $aC['tagBg'] ?>;
										 color:<?= $aC['tagColor'] ?>;">
								<?= htmlspecialchars($aC['tag']) ?>
							</span>
						</div>
					<?php else: ?>
						<?= htmlspecialchars($accounts) ?>
					<?php endif; ?>
				</td>

				<!-- Confirmation Statement -->
				<td class="td-nowrap <?= $conf_stmt === '—' ? 'td-muted' : '' ?>">
					<?php if ($cC['cellColor'] !== ''): ?>
						<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
							<span style="width:8px;height:8px;border-radius:50%;
										 background:<?= $cC['cellColor'] ?>;
										 display:inline-block;flex-shrink:0;"></span>
							<span style="color:<?= $cC['cellColor'] ?>;font-weight:700;">
								<?= htmlspecialchars($conf_stmt) ?>
							</span>
							<span style="font-size:10px;font-weight:600;padding:1px 6px;
										 border-radius:3px;
										 background:<?= $cC['tagBg'] ?>;
										 color:<?= $cC['tagColor'] ?>;">
								<?= htmlspecialchars($cC['tag']) ?>
							</span>
						</div>
					<?php else: ?>
						<?= htmlspecialchars($conf_stmt) ?>
					<?php endif; ?>
				</td>

                <!-- Officers -->
				<td class="td-muted" style="font-size:var(--text-body-sm);">
					<?= join_names_for_table($officersList, 'role') ?: '—' ?>
				</td>

                <!-- PSCs -->
				<td class="td-muted" style="font-size:var(--text-body-sm);">
					<?= join_names_for_table($pscsList) ?: '—' ?>
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


<?php include __DIR__ . '/theme/layout/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.clickable-row').forEach(function (row) {
    row.addEventListener('click', function (e) {

      // Prevent click if user clicked a button/link inside row
      if (e.target.closest('[data-no-row-click], a, button')) {
        return;
      }

      const href = this.getAttribute('data-href');
      if (href) {
        window.location.href = href;
      }
    });
  });
});
</script>