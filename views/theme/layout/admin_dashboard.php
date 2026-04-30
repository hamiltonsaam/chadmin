<?php
$pageTitle  = 'Admin Dashboard';
$activeLink = 'dashboard.php';
$userId     = $_SESSION['user_id'] ?? 'Admin';

include __DIR__ . '/header.php';
?>

<!-- ═══════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════ -->
<div class="page-header">
  <h1>Admin Dashboard</h1>
  <p>System-wide overview of filings, compliance activity and user administration.</p>
</div>


<!-- ═══════════════════════════════════════
     STAT CARDS — ROW 1 (System KPIs)
═══════════════════════════════════════ -->
<div class="stat-grid">

  <div class="stat-card">
    <div class="stat-label">Total Companies</div>
    <div class="stat-value">
      <?= number_format($totalCompanies ?? 14205) ?>
    </div>
    <div class="stat-meta stat-up">↑ 1.2% this month</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Active Filings</div>
    <div class="stat-value">
      <?= number_format($activeFilings ?? 3842) ?>
    </div>
    <div class="stat-meta">Across all users</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Pending Reviews</div>
    <div class="stat-value stat-warn">
      <?= number_format($pendingReviews ?? 845) ?>
    </div>
    <div class="stat-meta stat-warn">Requires attention</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">System Uptime</div>
    <div class="stat-value stat-up">
      <?= $systemUptime ?? '99.9%' ?>
    </div>
    <div class="stat-meta">Last 30 days</div>
  </div>

</div>


<!-- ═══════════════════════════════════════
     TWO-COLUMN ROW: Activity Log + User Admin
═══════════════════════════════════════ -->
<div style="display:grid; grid-template-columns: 1fr 380px; gap: var(--space-6);">


  <!-- ── System Activity Log ── -->
  <div class="table-container">

    <div class="topbar" style="height:auto; padding: var(--space-4) var(--space-6); border-bottom: 1px solid var(--outline-variant); border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
      <div class="topbar-left">
        <span class="material-symbols-outlined card-icon">history</span>
        <h2 class="card-title" style="margin:0;">System Activity Log</h2>
      </div>
      <a href="activity-log.php" class="inline-link">
        View all <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
      </a>
    </div>

    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Event Type</th>
            <th>User / System</th>
            <th class="th-center">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Replace this with your real DB query results
          $activityLog = $activityLog ?? [
            ['ts' => '2023-10-27 14:32', 'event' => 'Bulk Filing Ingestion',   'actor' => 'System Process',    'status' => 'Success'],
            ['ts' => '2023-10-27 14:28', 'event' => 'Admin Login',             'actor' => 'user.ch-99210',     'status' => 'Success'],
            ['ts' => '2023-10-27 14:15', 'event' => 'API Rate Limit Exceeded', 'actor' => 'External Partner A','status' => 'Warning'],
            ['ts' => '2023-10-27 13:50', 'event' => 'Database Sync',           'actor' => 'System Process',    'status' => 'Success'],
            ['ts' => '2023-10-27 13:30', 'event' => 'User Account Locked',     'actor' => 'user.ch-00412',     'status' => 'Error'],
            ['ts' => '2023-10-27 12:55', 'event' => 'Filing Submission',       'actor' => 'user.ch-88301',     'status' => 'Success'],
          ];

          // Map status → CSS class
          $statusClass = [
            'Success' => 'log-success',
            'Warning' => 'log-warning',
            'Error'   => 'log-error',
          ];

          foreach ($activityLog as $i => $row):
            $rowClass = ($i % 2 !== 0) ? 'row-alt' : '';
            $sClass   = $statusClass[$row['status']] ?? '';
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="td-mono td-nowrap"><?= htmlspecialchars($row['ts']) ?></td>
            <td class="td-medium"><?= htmlspecialchars($row['event']) ?></td>
            <td class="td-muted"><?= htmlspecialchars($row['actor']) ?></td>
            <td class="td-center <?= $sClass ?>"><?= htmlspecialchars($row['status']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div><!-- /.table-container (activity log) -->


  <!-- ── User Administration ── -->
  <div class="card" style="display:flex; flex-direction:column; gap: var(--space-6);">

    <div class="card-title-row">
      <span class="material-symbols-outlined card-icon">manage_accounts</span>
      <h2 class="card-title" style="margin:0;">User Administration</h2>
    </div>

    <!-- User stat mini-cards -->
    <div style="display:flex; flex-direction:column; gap: var(--space-3);">

      <div class="stat-card" style="display:flex; align-items:center; justify-content:space-between; padding: var(--space-3) var(--space-4);">
        <div>
          <div class="stat-label">Internal Admins</div>
          <div class="stat-value" style="font-size: var(--text-h3);">
            <?= number_format($internalAdmins ?? 142) ?>
          </div>
        </div>
        <span class="material-symbols-outlined" style="font-size:32px; color: var(--primary-container); opacity:0.5;">admin_panel_settings</span>
      </div>

      <div class="stat-card" style="display:flex; align-items:center; justify-content:space-between; padding: var(--space-3) var(--space-4);">
        <div>
          <div class="stat-label">External Presenters</div>
          <div class="stat-value" style="font-size: var(--text-h3);">
            <?= number_format($externalPresenters ?? 45920) ?>
          </div>
        </div>
        <span class="material-symbols-outlined" style="font-size:32px; color: var(--primary-container); opacity:0.5;">group</span>
      </div>

      <div class="stat-card" style="display:flex; align-items:center; justify-content:space-between; padding: var(--space-3) var(--space-4);">
        <div>
          <div class="stat-label">Locked Accounts</div>
          <div class="stat-value stat-err" style="font-size: var(--text-h3);">
            <?= number_format($lockedAccounts ?? 38) ?>
          </div>
        </div>
        <span class="material-symbols-outlined" style="font-size:32px; color: var(--error); opacity:0.5;">lock</span>
      </div>

    </div>

    <!-- Quick actions -->
    <div style="display:flex; flex-direction:column; gap: var(--space-2); margin-top:auto;">
      <a href="users.php" class="btn btn-primary btn-full">
        <span class="material-symbols-outlined" style="font-size:18px;">manage_accounts</span>
        Manage Users
      </a>
      <a href="locked-accounts.php" class="btn btn-secondary btn-full">
        <span class="material-symbols-outlined" style="font-size:18px;">lock_open</span>
        Review Locked Accounts
      </a>
    </div>

  </div><!-- /.card (user admin) -->

</div><!-- /.two-column grid -->

<?php include __DIR__ . '/footer.php'; ?>