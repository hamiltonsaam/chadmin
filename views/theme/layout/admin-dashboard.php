<?php
// ── Admin access guard — uncomment when auth is ready ───
// if (empty($_SESSION['is_admin'])) {
//     header('Location: login.php');
//     exit;
// }

$pageTitle  = 'Admin Dashboard';
$activeLink = 'admin-dashboard.php';

// Sample data — remove when using real queries
$stats = [
    'total_companies' => 14205,
    'active_filings'  => 3842,
    'pending_reviews' => 845,
    'uptime'          => '99.9%',
];

$activityLog = [
    ['timestamp' => '2026-04-23 09:47', 'event' => 'Bulk Filing Ingestion',     'actor' => 'System Process',     'status' => 'Success'],
    ['timestamp' => '2026-04-23 09:43', 'event' => 'Admin Login',               'actor' => 'user.ch-99210',      'status' => 'Success'],
    ['timestamp' => '2026-04-23 09:31', 'event' => 'API Rate Limit Exceeded',   'actor' => 'External Partner A', 'status' => 'Warning'],
    ['timestamp' => '2026-04-23 09:15', 'event' => 'Database Sync',             'actor' => 'System Process',     'status' => 'Success'],
    ['timestamp' => '2026-04-23 08:58', 'event' => 'New User Registration',     'actor' => 'user.ch-10045',      'status' => 'Success'],
    ['timestamp' => '2026-04-23 08:44', 'event' => 'Failed Login Attempt',      'actor' => 'Unknown',            'status' => 'Error'],
    ['timestamp' => '2026-04-23 08:30', 'event' => 'Scheduled Report Generated','actor' => 'System Process',     'status' => 'Success'],
    ['timestamp' => '2026-04-23 08:12', 'event' => 'Account Locked',            'actor' => 'user.ch-00391',      'status' => 'Warning'],
];

$userStats = [
    'internal_admins'     => 142,
    'external_presenters' => 45920,
    'locked_accounts'     => 38,
];

$logStatusClass = [
    'Success' => 'log-success',
    'Warning' => 'log-warning',
    'Error'   => 'log-error',
];

$logStatusIcon = [
    'Success' => 'check_circle',
    'Warning' => 'warning',
    'Error'   => 'cancel',
];

include __DIR__ . '/header.php';
?>


<!-- PAGE HEADER -->
<div class="page-header">
  <h1>Admin Dashboard</h1>
  <p>System-wide overview of filings, compliance activity and user administration.</p>
</div>


<!-- SYSTEM STAT CARDS -->
<div class="stat-grid">

  <div class="stat-card">
    <div class="stat-label">Total Companies</div>
    <div class="stat-value"><?= number_format($stats['total_companies']) ?></div>
    <div class="stat-meta">Registered in the system</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Active Filings</div>
    <div class="stat-value stat-up"><?= number_format($stats['active_filings']) ?></div>
    <div class="stat-meta stat-up">Currently in progress</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">Pending Reviews</div>
    <div class="stat-value stat-warn"><?= number_format($stats['pending_reviews']) ?></div>
    <div class="stat-meta stat-warn">Awaiting admin action</div>
  </div>

  <div class="stat-card">
    <div class="stat-label">System Uptime</div>
    <div class="stat-value stat-up"><?= htmlspecialchars($stats['uptime']) ?></div>
    <div class="stat-meta">Last 30 days</div>
  </div>

</div>


<!-- TWO-COLUMN: Activity Log + User Admin -->
<div class="dashboard-grid" style="align-items:start;">


  <!-- LEFT: System Activity Log -->
  <div class="table-container">

    <div style="display:flex; align-items:center; justify-content:space-between;
                padding: var(--space-4) var(--space-6);
                border-bottom: 1px solid var(--outline-variant);">
      <div style="display:flex; align-items:center; gap: var(--space-3);">
        <span class="material-symbols-outlined card-icon">history</span>
        <h2 style="font-size: var(--text-h3); font-weight:600; color: var(--on-surface); margin:0;">
          System Activity Log
        </h2>
      </div>
      <a href="activity-log.php" class="inline-link">
        View full log
        <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
      </a>
    </div>

    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th class="td-nowrap">Timestamp</th>
            <th>Event Type</th>
            <th>User / System</th>
            <th class="th-center">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($activityLog as $i => $log):
            $statusClass = $logStatusClass[$log['status']] ?? '';
            $statusIcon  = $logStatusIcon[$log['status']]  ?? 'info';
            $rowClass    = $i % 2 !== 0 ? 'row-alt' : '';
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="td-mono td-nowrap"><?= htmlspecialchars($log['timestamp']) ?></td>
            <td class="td-medium"><?= htmlspecialchars($log['event']) ?></td>
            <td class="td-muted"><?= htmlspecialchars($log['actor']) ?></td>
            <td class="td-center td-nowrap">
              <span class="<?= $statusClass ?>" style="display:inline-flex; align-items:center; gap:4px;">
                <span class="material-symbols-outlined" style="font-size:15px;
                  font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 24;">
                  <?= $statusIcon ?>
                </span>
                <?= htmlspecialchars($log['status']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div><!-- /.table-container (log) -->


  <!-- RIGHT: User Administration -->
  <div style="display:flex; flex-direction:column; gap: var(--space-4);">

    <div class="card">
      <div class="card-title-row">
        <span class="material-symbols-outlined card-icon">admin_panel_settings</span>
        <h2 class="card-title card-title-inline">User Administration</h2>
      </div>

      <div style="display:flex; flex-direction:column; gap: var(--space-4);">

        <div style="display:flex; align-items:center; justify-content:space-between;
                    padding: var(--space-3) var(--space-4);
                    background: var(--surface-container-low);
                    border-radius: var(--radius-md);
                    border: 1px solid var(--outline-variant);">
          <div>
            <p class="stat-label" style="margin-bottom: var(--space-1);">Internal Admins</p>
            <p style="font-size: var(--text-h3); font-weight:700; color: var(--on-surface); margin:0;">
              <?= number_format($userStats['internal_admins']) ?>
            </p>
          </div>
          <span class="material-symbols-outlined" style="color: var(--primary-container); font-size:28px;">
            manage_accounts
          </span>
        </div>

        <div style="display:flex; align-items:center; justify-content:space-between;
                    padding: var(--space-3) var(--space-4);
                    background: var(--surface-container-low);
                    border-radius: var(--radius-md);
                    border: 1px solid var(--outline-variant);">
          <div>
            <p class="stat-label" style="margin-bottom: var(--space-1);">External Presenters</p>
            <p style="font-size: var(--text-h3); font-weight:700; color: var(--on-surface); margin:0;">
              <?= number_format($userStats['external_presenters']) ?>
            </p>
          </div>
          <span class="material-symbols-outlined" style="color: var(--primary-container); font-size:28px;">
            groups
          </span>
        </div>

        <div style="display:flex; align-items:center; justify-content:space-between;
                    padding: var(--space-3) var(--space-4);
                    background: var(--error-container);
                    border-radius: var(--radius-md);
                    border: 1px solid rgba(186,26,26,0.2);">
          <div>
            <p class="stat-label" style="margin-bottom: var(--space-1); color: var(--on-error-container);">
              Locked Accounts
            </p>
            <p style="font-size: var(--text-h3); font-weight:700; color: var(--on-error-container); margin:0;">
              <?= number_format($userStats['locked_accounts']) ?>
            </p>
          </div>
          <span class="material-symbols-outlined" style="color: var(--on-error-container); font-size:28px;">
            lock_person
          </span>
        </div>

      </div><!-- /.user stat rows -->

      <div style="display:flex; flex-direction:column; gap: var(--space-2); margin-top: var(--space-6);">
        <a href="user-management.php" class="btn btn-primary btn-full">
          <span class="material-symbols-outlined" style="font-size:18px;">group</span>
          Manage Users
        </a>
        <a href="locked-accounts.php" class="btn btn-secondary btn-full">
          <span class="material-symbols-outlined" style="font-size:18px;">lock_open</span>
          Review Locked Accounts
        </a>
      </div>

    </div><!-- /.card -->

    <div class="card" style="background: var(--surface-container-low); border-color: var(--outline-variant);">
      <div style="display:flex; gap: var(--space-3); align-items:flex-start;">
        <span class="material-symbols-outlined" style="color:#137333; font-size:22px; flex-shrink:0; margin-top:2px;
          font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 24;">
          check_circle
        </span>
        <div>
          <p style="font-size: var(--text-body-sm); font-weight:600; color: var(--on-surface); margin-bottom: var(--space-1);">
            All systems operational
          </p>
          <p style="font-size: var(--text-body-sm); color: var(--on-surface-variant); line-height:1.6;">
            Last checked: <?= date('d M Y, H:i') ?> BST
          </p>
        </div>
      </div>
    </div>

  </div><!-- /.right column -->

</div><!-- /.dashboard-grid -->


<?php include __DIR__ . '/footer.php'; ?>