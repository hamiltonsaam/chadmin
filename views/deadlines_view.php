<?php
/**
 * =========================================================
 * DEADLINES VIEW
 * =========================================================
 *
 * PURPOSE
 * -------
 * Shows only companies with:
 * - overdue deadlines
 * - due-soon deadlines
 *
 * DATA SOURCE
 * -----------
 * Controller must provide:
 * - $deadlineCompanies
 * - $categories
 * - $categoryFilter
 *
 * Each company row should contain:
 * - _deadline_risk
 * - _accounts_due
 * - _confirmation_due
 *
 * ROW CLICK
 * ---------
 * Whole table row links to:
 * company_details.php?company=COMPANY_NUMBER
 */

$activePage = 'deadlines';
$pageTitle  = 'Deadlines';

require __DIR__ . '/theme/layout/header.php';


/**
 * =========================================================
 * BADGE STYLE MAP
 * =========================================================
 */
$badgeMap = [
    'overdue'  => [
        'label' => 'Overdue',
        'bg'    => '#ffdad6',
        'color' => '#93000a',
        'dot'   => 'var(--error)',
    ],
    'due_soon' => [
        'label' => 'Due Soon',
        'bg'    => '#fff7ed',
        'color' => '#b45309',
        'dot'   => '#d97706',
    ],
];


/**
 * =========================================================
 * LOCAL DATE HELPERS
 * =========================================================
 */

function deadline_days_text(string $date): string
{
    $date = trim($date);

    if ($date === '') {
        return '—';
    }

    try {
        $target = new DateTimeImmutable($date);
        $today  = new DateTimeImmutable('today');

        $days = (int) $today->diff($target)->format('%r%a');

        if ($days < 0) {
            return abs($days) . ' days overdue';
        }

        if ($days === 0) {
            return 'Due today';
        }

        return $days . ' days left';
    } catch (Throwable) {
        return '—';
    }
}

function deadline_date_style(string $date): string
{
    $date = trim($date);

    if ($date === '') {
        return '';
    }

    try {
        $target = new DateTimeImmutable($date);
        $today  = new DateTimeImmutable('today');

        $days = (int) $today->diff($target)->format('%r%a');

        if ($days <= 0) {
            return 'color:#93000a;font-weight:700;';
        }

        if ($days <= 30) {
            return 'color:#b45309;font-weight:700;';
        }

        return 'color:#166534;font-weight:700;';
    } catch (Throwable) {
        return '';
    }
}

/**
 * =========================================================
 * PAGINATION
 * =========================================================
 */
$perPage     = 10;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalCount  = count($deadlineCompanies);
$totalPages  = max(1, (int) ceil($totalCount / $perPage));
$currentPage = min($currentPage, $totalPages);
$startRow    = ($currentPage - 1) * $perPage + 1;
$endRow      = min($currentPage * $perPage, $totalCount);
$pageRows    = array_slice($deadlineCompanies, ($currentPage - 1) * $perPage, $perPage);
?>

<!-- =========================================================
     PAGE HEADER / FILTER
========================================================= -->
<div class="card" style="margin-bottom:var(--space-5);">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:var(--space-4);flex-wrap:wrap;">
        <div>
            <h1 style="margin:0;">Deadlines</h1>
            <p style="margin:4px 0 0;color:var(--on-surface-variant);">
                Companies with overdue or upcoming Companies House deadlines.
            </p>
        </div>

        <form method="get" action="deadlines.php" style="display:flex;gap:var(--space-3);align-items:center;">
            <select name="category" class="select" onchange="this.form.submit()">
                <option value="">All categories</option>

                <?php foreach ($categories as $cat): ?>
                    <option value="<?= h((string) $cat) ?>" <?= $categoryFilter === (string) $cat ? 'selected' : '' ?>>
                        <?= h((string) $cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($categoryFilter !== ''): ?>
                <a href="deadlines.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>


<!-- =========================================================
     DEADLINES TABLE
========================================================= -->
<div class="table-container">

    <div class="table-scroll">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Company No.</th>
                    <th>Company Name</th>
                    <th class="th-center">Accounts Due</th>
                    <th class="th-center">Accounts Days</th>
                    <th class="th-center">Confirmation Due</th>
                    <th class="th-center">Confirmation Days</th>
                    <th class="th-center">Risk</th>
                    <th class="th-right">Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($pageRows)): ?>

                <tr>
                    <td colspan="8" style="text-align:center; padding:var(--space-12); color:var(--on-surface-variant);">
                        <span class="material-symbols-outlined"
                              style="font-size:36px; display:block; margin:0 auto var(--space-3);">
                            event_available
                        </span>
                        No overdue or due-soon companies found.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($pageRows as $i => $company): ?>
                    <?php
                    $companyNumber = (string) ($company['company_number'] ?? '');
                    $companyName   = (string) company_display_name_with_label($company);

                    $risk        = (string) ($company['_deadline_risk'] ?? 'due_soon');
                    $accountsDue = (string) ($company['_accounts_due'] ?? '');
                    $confirmDue  = (string) ($company['_confirmation_due'] ?? '');

                    $rowClass = $i % 2 !== 0 ? 'row-alt' : '';
                    $badge    = $badgeMap[$risk] ?? $badgeMap['due_soon'];

                    $companyUrl = 'company_details.php?company=' . urlencode($companyNumber);

                    $accountsDateStyle = deadline_date_style($accountsDue);
                    $confirmDateStyle  = deadline_date_style($confirmDue);

                    $accountsDaysText = deadline_days_text($accountsDue);
                    $confirmDaysText  = deadline_days_text($confirmDue);
                    ?>

                    <tr class="<?= h($rowClass) ?>"
                        onclick="window.location.href='<?= h($companyUrl) ?>'"
                        style="cursor:pointer;">

                        <!-- Company number -->
                        <td class="td-mono td-nowrap">
                            <?= h($companyNumber) ?>
                        </td>

                        <!-- Company name -->
                        <td class="td-medium">
                            <?= h($companyName) ?>
                        </td>

                        <td class="td-center td-nowrap" style="<?= h($accountsDateStyle) ?>">
                            <?= h($accountsDue !== '' ? $accountsDue : '—') ?>
                        </td>

                        <td class="td-center td-nowrap" style="<?= h($accountsDateStyle) ?>">
                            <?= h($accountsDaysText) ?>
                        </td>

                        <td class="td-center td-nowrap" style="<?= h($confirmDateStyle) ?>">
                            <?= h($confirmDue !== '' ? $confirmDue : '—') ?>
                        </td>

                        <td class="td-center td-nowrap" style="<?= h($confirmDateStyle) ?>">
                            <?= h($confirmDaysText) ?>
                        </td>
                        <!-- Risk badge -->
                        <td class="td-center">
                            <span class="badge" style="
                                background:<?= h($badge['bg']) ?>;
                                color:<?= h($badge['color']) ?>;
                                display:inline-flex;
                                align-items:center;
                                gap:5px;
                            ">
                                <span style="
                                    width:7px;
                                    height:7px;
                                    border-radius:50%;
                                    background:<?= h($badge['dot']) ?>;
                                    flex-shrink:0;
                                    display:inline-block;
                                "></span>
                                <?= h($badge['label']) ?>
                            </span>
                        </td>

                        <!-- Action button -->
                        <td class="td-right td-nowrap">
                            <a href="<?= h($companyUrl) ?>"
                               class="btn btn-secondary btn-sm"
                               onclick="event.stopPropagation();">
                                View
                                <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
                            </a>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>
        </table>
    </div>


    <!-- =========================================================
         PAGINATION
    ========================================================= -->
    <?php if ($totalCount > 0): ?>
        <div class="pagination">

            <span class="pagination-info">
                Showing <?= $startRow ?>–<?= $endRow ?> of <?= $totalCount ?> companies
            </span>

            <div class="pagination-controls">

                <?php if ($currentPage > 1): ?>
                    <a href="?<?= h(http_build_query(array_merge($_GET, ['page' => $currentPage - 1]))) ?>"
                       class="pg-btn" aria-label="Previous page">
                        <span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
                    </a>
                <?php else: ?>
                    <button class="pg-btn" disabled aria-label="Previous page">
                        <span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
                    </button>
                <?php endif; ?>

                <?php
                $range = 2;

                for ($p = 1; $p <= $totalPages; $p++):
                    if ($p === 1 || $p === $totalPages || ($p >= $currentPage - $range && $p <= $currentPage + $range)):
                ?>

                    <?php if ($p > 1 && $p === $currentPage - $range && $currentPage - $range > 2): ?>
                        <span class="pg-ellipsis">…</span>
                    <?php endif; ?>

                    <a href="?<?= h(http_build_query(array_merge($_GET, ['page' => $p]))) ?>"
                       class="pg-btn <?= $p === $currentPage ? 'active' : '' ?>"
                       aria-label="Page <?= $p ?>"
                       <?= $p === $currentPage ? 'aria-current="page"' : '' ?>>
                        <?= $p ?>
                    </a>

                    <?php if ($p < $totalPages && $p === $currentPage + $range && $currentPage + $range < $totalPages - 1): ?>
                        <span class="pg-ellipsis">…</span>
                    <?php endif; ?>

                <?php
                    endif;
                endfor;
                ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?<?= h(http_build_query(array_merge($_GET, ['page' => $currentPage + 1]))) ?>"
                       class="pg-btn" aria-label="Next page">
                        <span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
                    </a>
                <?php else: ?>
                    <button class="pg-btn" disabled aria-label="Next page">
                        <span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
                    </button>
                <?php endif; ?>

            </div>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/theme/layout/footer.php'; ?>