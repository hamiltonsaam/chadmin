<?php
$pageTitle  = 'Managed Companies';
$activeLink = 'managed-companies.php';

// ── Filters from GET params ──────────────────────────────
$search    = trim($_GET['search']   ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 10;

// ── Replace with your real DB query ─────────────────────
// $companies   = getCompaniesPaginated($search, $filterStatus, $page, $perPage);
// $totalCount  = getCompaniesCount($search, $filterStatus);
// $totalPages  = ceil($totalCount / $perPage);
// $startRow    = ($page - 1) * $perPage + 1;
// $endRow      = min($page * $perPage, $totalCount);

// Sample data — remove when using real query
$allCompanies = [
  ['number'=>'09876543','name'=>'Acme Global Solutions Ltd.','address'=>'123 Corporate Square, London, EC1A 1BB','status'=>'Active','accounts'=>'30 Sep 2025','conf_stmt'=>'15 Nov 2025','officers'=>3,'pscs'=>2],
  ['number'=>'11223344','name'=>'Pinnacle Consulting LLP','address'=>'45 High Street, Manchester, M1 4EE','status'=>'Active','accounts'=>'31 Dec 2025','conf_stmt'=>'01 Feb 2025','officers'=>5,'pscs'=>4],
  ['number'=>'05544332','name'=>'Oldstone Manufacturing Co.','address'=>'Industrial Park Road, Birmingham, B2 2BB','status'=>'Dissolved','accounts'=>'—','conf_stmt'=>'—','officers'=>0,'pscs'=>0],
  ['number'=>'13456789','name'=>'Nexus Technologies Ltd','address'=>'Innovation Hub, Science Way, Cambridge, CB2 1TN','status'=>'Liquidation','accounts'=>'31 Mar 2025','conf_stmt'=>'10 May 2025','officers'=>2,'pscs'=>1],
  ['number'=>'15566778','name'=>'Green Energy Ventures PLC','address'=>'Eco Tower, Riverside, Bristol, BS1 6DZ','status'=>'Active','accounts'=>'30 Jun 2025','conf_stmt'=>'12 Aug 2025','officers'=>8,'pscs'=>3],
  ['number'=>'07123456','name'=>'Albion Digital Services Ltd','address'=>'Suite 10, Digital Quarter, Leeds, LS1 4AX','status'=>'Active','accounts'=>'28 Feb 2025','conf_stmt'=>'30 Mar 2025','officers'=>4,'pscs'=>2],
  ['number'=>'03341122','name'=>'Northern Freight Solutions Ltd','address'=>'Unit 7, Cargo Road, Sheffield, S1 2AB','status'=>'Dormant','accounts'=>'31 Jan 2026','conf_stmt'=>'14 Feb 2026','officers'=>2,'pscs'=>1],
];

// Apply filters on sample data
$filtered = array_filter($allCompanies, function($c) use ($search, $filterStatus) {
  $matchSearch = !$search ||
    stripos($c['name'],   $search) !== false ||
    stripos($c['number'], $search) !== false ||
    stripos($c['address'],$search) !== false;
  $matchStatus = !$filterStatus || $c['status'] === $filterStatus;
  return $matchSearch && $matchStatus;
});
$filtered    = array_values($filtered);
$totalCount  = count($filtered);
$totalPages  = max(1, (int)ceil($totalCount / $perPage));
$page        = min($page, $totalPages);
$startRow    = ($page - 1) * $perPage + 1;
$endRow      = min($page * $perPage, $totalCount);
$companies   = array_slice($filtered, ($page - 1) * $perPage, $perPage);

// Badge CSS class map
$badgeMap = [
  'Active'      => 'badge-active',
  'Dissolved'   => 'badge-dissolved',
  'Liquidation' => 'badge-liquidation',
  'Dormant'     => 'badge-dormant',
];

include __DIR__ . '/header.php';
?>


<!-- ═══════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════ -->
<div class="page-header">
  <h1>Managed Companies</h1>
  <p>View and manage your full portfolio of companies and their compliance status.</p>
</div>


<!-- ═══════════════════════════════════════
     FILTER BAR
═══════════════════════════════════════ -->
<form method="GET" action="managed-companies.php" class="filter-bar">
  <div class="filter-bar-controls">

    <!-- Search -->
    <div class="input-wrap" style="flex:1; min-width:200px;">
      <span class="material-symbols-outlined input-icon">search</span>
      <input
        type="text"
        name="search"
        class="input input-padded"
        placeholder="Search by name, number or address…"
        value="<?= htmlspecialchars($search) ?>"
        autocomplete="off"
      />
    </div>

    <!-- Status filter -->
    <div class="select-wrap" style="min-width:160px;">
      <select name="status" class="select">
        <option value="">All statuses</option>
        <?php foreach (['Active','Dissolved','Liquidation','Dormant'] as $s): ?>
        <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>>
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

    <!-- Clear (only shown when filters are active) -->
    <?php if ($search || $filterStatus): ?>
    <a href="managed-companies.php" class="btn btn-secondary">
      <span class="material-symbols-outlined" style="font-size:18px;">close</span>
      Clear
    </a>
    <?php endif; ?>

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
          <th>Company No.</th>
          <th>Company Name</th>
          <th class="th-address">Registered Address</th>
          <th class="th-center">Status</th>
          <th>Next Accounts</th>
          <th>Conf. Statement</th>
          <th class="th-center">Officers</th>
          <th class="th-center">PSCs</th>
          <th class="th-right">Actions</th>
        </tr>
      </thead>
      <tbody>

        <?php if (empty($companies)): ?>
        <tr>
          <td colspan="9" style="text-align:center; padding: var(--space-12); color: var(--on-surface-variant);">
            <span class="material-symbols-outlined" style="font-size:36px; display:block; margin: 0 auto var(--space-3);">
              domain_disabled
            </span>
            No companies match your search.
          </td>
        </tr>

        <?php else: ?>
        <?php foreach ($companies as $i => $c):
          $badge      = $badgeMap[$c['status']] ?? 'badge-neutral';
          $isDissolved = in_array($c['status'], ['Dissolved','Liquidation']);
          $rowClass   = $i % 2 !== 0 ? 'row-alt' : '';
        ?>
        <tr class="<?= $rowClass ?>">

          <!-- Company number -->
          <td class="td-mono td-nowrap"><?= htmlspecialchars($c['number']) ?></td>

          <!-- Company name — strikethrough if dissolved/liquidation -->
          <td class="td-medium <?= $isDissolved ? 'td-strike' : '' ?>">
            <?= htmlspecialchars($c['name']) ?>
          </td>

          <!-- Address -->
          <td class="td-muted"><?= htmlspecialchars($c['address']) ?></td>

          <!-- Status badge -->
          <td class="td-center">
            <span class="badge <?= $badge ?>">
              <?= htmlspecialchars($c['status']) ?>
            </span>
          </td>

          <!-- Next Accounts -->
          <td class="td-nowrap <?= $c['accounts'] === '—' ? 'td-muted' : '' ?>">
            <?= htmlspecialchars($c['accounts']) ?>
          </td>

          <!-- Confirmation Statement -->
          <td class="td-nowrap <?= $c['conf_stmt'] === '—' ? 'td-muted' : '' ?>">
            <?= htmlspecialchars($c['conf_stmt']) ?>
          </td>

          <!-- Officers count -->
          <td class="td-center td-nowrap">
            <?php if ($c['officers'] > 0): ?>
              <span class="badge badge-neutral"><?= $c['officers'] ?></span>
            <?php else: ?>
              <span class="td-muted">—</span>
            <?php endif; ?>
          </td>

          <!-- PSC count -->
          <td class="td-center td-nowrap">
            <?php if ($c['pscs'] > 0): ?>
              <span class="badge badge-neutral"><?= $c['pscs'] ?></span>
            <?php else: ?>
              <span class="td-muted">—</span>
            <?php endif; ?>
          </td>

          <!-- View button -->
          <td class="td-right td-nowrap">
            <a href="company-details.php?id=<?= urlencode($c['number']) ?>" class="btn btn-secondary btn-sm">
              View
              <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            </a>
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
      <?php if ($page > 1): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
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
      $range = 2; // pages either side of current
      for ($p = 1; $p <= $totalPages; $p++):
        if ($p === 1 || $p === $totalPages || ($p >= $page - $range && $p <= $page + $range)):
      ?>
        <?php if ($p > 1 && $p < $page - $range): ?>
          <span class="pg-ellipsis">…</span>
        <?php endif; ?>

        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
           class="pg-btn <?= $p === $page ? 'active' : '' ?>"
           aria-label="Page <?= $p ?>"
           <?= $p === $page ? 'aria-current="page"' : '' ?>>
          <?= $p ?>
        </a>

        <?php if ($p < $totalPages && $p > $page + $range): ?>
          <span class="pg-ellipsis">…</span>
        <?php endif; ?>

      <?php endif; endfor; ?>

      <!-- Next -->
      <?php if ($page < $totalPages): ?>
      <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
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


<?php include __DIR__ . '/footer.php'; ?>