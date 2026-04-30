<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$companyId = trim($_GET['id'] ?? '');
if (empty($companyId)) {
    header('Location: /chadmin/views/theme/layout/managed-companies.php');
    exit;
}

$section       = $_GET['section'] ?? 'overview';
$validSections = ['overview','filings','officers','psc','address'];
if (!in_array($section, $validSections)) $section = 'overview';

// Sample data — replace with real DB data
$company = [
    'number'          => $companyId,
    'name'            => 'Acme Global Solutions Ltd.',
    'jurisdiction'    => 'Registered in England &amp; Wales',
    'status'          => 'Active',
    'type'            => 'Private Limited Company',
    'incorporated'    => '15 March 2010',
    'sic'             => '62020 — Information technology consultancy activities',
    'accounts_due'    => '31 Dec 2025',
    'accounts_made'   => '31 March 2025',
    'accounts_last'   => '31 March 2024',
    'conf_due'        => '14 Apr 2025',
    'conf_made'       => '31 March 2025',
    'conf_last'       => '31 March 2024',
    'address_line1'   => '123 Corporate Square',
    'address_line2'   => '',
    'address_town'    => 'London',
    'address_post'    => 'EC1A 1BB',
    'address_country' => 'England',
];

$officers = [
    ['name' => 'James A. Mitchell',  'role' => 'Director',          'appointed' => '15 Mar 2010'],
    ['name' => 'Sarah L. Thornton',  'role' => 'Director',          'appointed' => '01 Jun 2019'],
    ['name' => 'R. Patel &amp; Co.', 'role' => 'Company Secretary', 'appointed' => '10 Jan 2021'],
];

$pscs = [
    ['name' => 'James A. Mitchell', 'nature' => '75–100% voting rights &amp; shares'],
];

$filings = [
    ['date' => '20 Dec 2024', 'description' => 'Full accounts made up to 31 March 2024',                   'form' => 'AA'],
    ['date' => '15 Apr 2024', 'description' => 'Confirmation statement made on 31 March 2024 with updates', 'form' => 'CS01'],
    ['date' => '02 Nov 2023', 'description' => 'Change of registered office address',                       'form' => 'AD01'],
    ['date' => '18 Dec 2023', 'description' => 'Full accounts made up to 31 March 2023',                   'form' => 'AA'],
    ['date' => '10 Apr 2023', 'description' => 'Confirmation statement with no updates',                    'form' => 'CS01'],
];

$badgeMap    = ['Active'=>'badge-active','Dissolved'=>'badge-dissolved','Liquidation'=>'badge-liquidation','Dormant'=>'badge-dormant'];
$statusBadge = $badgeMap[$company['status']] ?? 'badge-neutral';

$userId    = $_SESSION['user_id'] ?? 'CH-99210';
$isAdmin   = !empty($_SESSION['is_admin']);
$pageTitle = $company['name'];
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?> — ComplianceHub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
  <link rel="stylesheet" href="/chadmin/views/theme/assets/css/styles.css" />
  <link rel="stylesheet" href="/chadmin/views/theme/assets/css/mobile.css" />
  <style>
    body.company-page {
      display: block !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
    }
  </style>
</head>
<body class="company-page">

<?php include __DIR__ . '/topbar.php'; ?>


<!-- COMPANY SECONDARY SIDEBAR -->
<nav class="company-sidebar" aria-label="Company navigation">

  <div class="company-sidebar-top">
    <p class="company-sidebar-title"><?= htmlspecialchars($company['name']) ?></p>
    <p class="company-sidebar-subtitle"><?= htmlspecialchars($company['number']) ?></p>
  </div>

  <ul class="company-sidebar-menu">
    <?php
    $navItems = [
      'overview' => ['icon' => 'dashboard',      'label' => 'Overview'],
      'filings'  => ['icon' => 'description',     'label' => 'Filings'],
      'officers' => ['icon' => 'manage_accounts', 'label' => 'Officers'],
      'psc'      => ['icon' => 'verified_user',   'label' => 'PSC Register'],
      'address'  => ['icon' => 'location_on',     'label' => 'Registered Address'],
    ];
    foreach ($navItems as $key => $item):
      $isActive = ($section === $key);
      $url = '/chadmin/views/theme/layout/company-details.php?id=' . urlencode($companyId) . '&section=' . $key;
    ?>
    <li>
      <a href="<?= $url ?>" class="company-sidebar-link <?= $isActive ? 'active' : '' ?>">
        <span class="material-symbols-outlined"><?= $item['icon'] ?></span>
        <?= $item['label'] ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>

  <div class="company-sidebar-footer">
    <a href="/chadmin/views/theme/layout/managed-companies.php" class="company-sidebar-link">
      <span class="material-symbols-outlined">arrow_back</span>
      All Companies
    </a>
  </div>

</nav>


<!-- MAIN CONTENT -->
<main class="company-main">
<div class="company-content">


  <!-- Company Header -->
  <div class="company-header">
    <div class="company-header-row">
      <div>
        <h1 class="company-title"><?= htmlspecialchars($company['name']) ?></h1>
        <p class="company-subtitle">
          Company number: <strong><?= htmlspecialchars($company['number']) ?></strong>
          &nbsp;·&nbsp; <?= $company['jurisdiction'] ?>
        </p>
      </div>
      <span class="company-status">
        <span class="material-symbols-outlined">
          <?= $company['status'] === 'Active' ? 'check_circle' : 'cancel' ?>
        </span>
        <?= htmlspecialchars($company['status']) ?>
      </span>
    </div>
  </div>


  <?php if ($section === 'overview'): ?>
  <!-- OVERVIEW -->
  <div class="company-layout">

    <div class="company-primary">

      <div class="card">
        <div class="card-title-row">
          <span class="material-symbols-outlined card-icon">info</span>
          <h2 class="card-title card-title-inline">Company Details</h2>
        </div>
        <div class="detail-grid">
          <div>
            <p class="meta-label">Company Type</p>
            <p class="meta-value"><?= htmlspecialchars($company['type']) ?></p>
          </div>
          <div>
            <p class="meta-label">Incorporated</p>
            <p class="meta-value"><?= htmlspecialchars($company['incorporated']) ?></p>
          </div>
          <div>
            <p class="meta-label">Status</p>
            <p class="meta-value">
              <span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($company['status']) ?></span>
            </p>
          </div>
          <div>
            <p class="meta-label">SIC Code</p>
            <p class="meta-value"><?= htmlspecialchars($company['sic']) ?></p>
          </div>
        </div>
      </div>

      <div class="panels-grid">

        <div class="card">
          <div class="card-title-row">
            <span class="material-symbols-outlined card-icon">receipt_long</span>
            <h2 class="card-title card-title-inline">Accounts</h2>
          </div>
          <div class="highlight-box">
            <p class="highlight-date"><?= htmlspecialchars($company['accounts_due']) ?></p>
            <p class="highlight-note highlight-note-spaced">Next accounts due</p>
            <p class="meta-label" style="margin-top:var(--space-2);">Made up to</p>
            <p class="meta-value text-strong"><?= htmlspecialchars($company['accounts_made']) ?></p>
          </div>
          <p class="meta-label">Last accounts</p>
          <p class="meta-value"><?= htmlspecialchars($company['accounts_last']) ?></p>
        </div>

        <div class="card">
          <div class="card-title-row">
            <span class="material-symbols-outlined card-icon">task_alt</span>
            <h2 class="card-title card-title-inline">Confirmation Statement</h2>
          </div>
          <div class="highlight-box">
            <p class="highlight-date"><?= htmlspecialchars($company['conf_due']) ?></p>
            <p class="highlight-note highlight-note-spaced">Next statement due</p>
            <p class="meta-label" style="margin-top:var(--space-2);">Made up to</p>
            <p class="meta-value text-strong"><?= htmlspecialchars($company['conf_made']) ?></p>
          </div>
          <p class="meta-label">Last statement</p>
          <p class="meta-value"><?= htmlspecialchars($company['conf_last']) ?></p>
        </div>

      </div>

      <div class="card card-flush">
        <div class="card-flush-header card-title-row">
          <span class="material-symbols-outlined card-icon">folder_open</span>
          <h2 class="card-title card-title-inline">Recent Filings</h2>
          <a href="/chadmin/views/theme/layout/company-details.php?id=<?= urlencode($companyId) ?>&section=filings"
             class="inline-link" style="margin-left:auto;">
            View all
            <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
          </a>
        </div>
        <div class="table-scroll">
          <table class="data-table filings-table">
            <thead>
              <tr>
                <th class="col-date">Date</th>
                <th>Description</th>
                <th class="col-form">Form</th>
                <th class="th-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($filings, 0, 3) as $filing): ?>
              <tr>
                <td class="td-muted td-nowrap"><?= htmlspecialchars($filing['date']) ?></td>
                <td><?= htmlspecialchars($filing['description']) ?></td>
                <td><span class="badge badge-neutral"><?= htmlspecialchars($filing['form']) ?></span></td>
                <td class="td-right">
                  <a href="#" class="inline-link">
                    <span class="material-symbols-outlined icon-sm">download</span>
                    Download
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /.company-primary -->


    <div class="company-secondary">

      <div class="card office-card card-flush">
        <div class="office-hero">
          <span class="material-symbols-outlined office-hero-icon">location_city</span>
        </div>
        <div style="padding:var(--space-6);">
          <div class="card-title-row">
            <span class="material-symbols-outlined card-icon">location_on</span>
            <h2 class="card-title card-title-inline">Registered Address</h2>
          </div>
          <address class="office-address">
            <?php if ($company['address_line1']): ?><?= htmlspecialchars($company['address_line1']) ?><br><?php endif; ?>
            <?php if ($company['address_line2']): ?><?= htmlspecialchars($company['address_line2']) ?><br><?php endif; ?>
            <?= htmlspecialchars($company['address_town']) ?><br>
            <?= htmlspecialchars($company['address_post']) ?><br>
            <?= htmlspecialchars($company['address_country']) ?>
          </address>
          <a href="https://maps.google.com/?q=<?= urlencode($company['address_line1'].', '.$company['address_town'].', '.$company['address_post']) ?>"
             target="_blank" rel="noopener noreferrer" class="inline-link" style="margin-top:var(--space-4);display:inline-flex;">
            View on map
            <span class="material-symbols-outlined icon-sm">open_in_new</span>
          </a>
        </div>
      </div>

      <div class="card">
        <div class="card-title-row">
          <span class="material-symbols-outlined card-icon">manage_accounts</span>
          <h2 class="card-title card-title-inline">Officers</h2>
          <span class="badge badge-neutral" style="margin-left:auto;"><?= count($officers) ?></span>
        </div>
        <ul class="people-list">
          <?php foreach ($officers as $officer): ?>
          <li class="person-row">
            <span class="material-symbols-outlined person-icon">person</span>
            <div>
              <p class="person-name"><?= $officer['name'] ?></p>
              <p class="person-role"><?= htmlspecialchars($officer['role']) ?> — appointed <?= htmlspecialchars($officer['appointed']) ?></p>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="card">
        <div class="card-title-row">
          <span class="material-symbols-outlined card-icon">verified_user</span>
          <h2 class="card-title card-title-inline">PSC Register</h2>
          <span class="badge badge-neutral" style="margin-left:auto;"><?= count($pscs) ?></span>
        </div>
        <ul class="people-list">
          <?php foreach ($pscs as $psc): ?>
          <li class="person-row">
            <span class="material-symbols-outlined person-icon">shield_person</span>
            <div>
              <p class="person-name"><?= $psc['name'] ?></p>
              <p class="person-role"><?= $psc['nature'] ?></p>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

    </div><!-- /.company-secondary -->

  </div><!-- /.company-layout -->


  <?php elseif ($section === 'filings'): ?>
  <div class="card card-flush">
    <div class="card-flush-header card-title-row">
      <span class="material-symbols-outlined card-icon">description</span>
      <h2 class="card-title card-title-inline">All Filings</h2>
    </div>
    <div class="table-scroll">
      <table class="data-table filings-table">
        <thead>
          <tr>
            <th class="col-date">Date</th>
            <th>Description</th>
            <th class="col-form">Form</th>
            <th class="th-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filings as $i => $filing): ?>
          <tr class="<?= $i % 2 !== 0 ? 'row-alt' : '' ?>">
            <td class="td-muted td-nowrap"><?= htmlspecialchars($filing['date']) ?></td>
            <td><?= htmlspecialchars($filing['description']) ?></td>
            <td><span class="badge badge-neutral"><?= htmlspecialchars($filing['form']) ?></span></td>
            <td class="td-right">
              <a href="#" class="inline-link">
                <span class="material-symbols-outlined icon-sm">download</span>
                Download
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>


  <?php elseif ($section === 'officers'): ?>
  <div class="card">
    <div class="card-title-row">
      <span class="material-symbols-outlined card-icon">manage_accounts</span>
      <h2 class="card-title card-title-inline">Officers</h2>
      <span class="badge badge-neutral" style="margin-left:auto;"><?= count($officers) ?> total</span>
    </div>
    <ul class="people-list">
      <?php foreach ($officers as $officer): ?>
      <li class="person-row" style="padding:var(--space-4); border-bottom:1px solid var(--outline-variant);">
        <span class="material-symbols-outlined person-icon">person</span>
        <div style="flex:1;">
          <p class="person-name"><?= $officer['name'] ?></p>
          <p class="person-role"><?= htmlspecialchars($officer['role']) ?> &nbsp;·&nbsp; Appointed <?= htmlspecialchars($officer['appointed']) ?></p>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>


  <?php elseif ($section === 'psc'): ?>
  <div class="card">
    <div class="card-title-row">
      <span class="material-symbols-outlined card-icon">verified_user</span>
      <h2 class="card-title card-title-inline">Persons with Significant Control</h2>
      <span class="badge badge-neutral" style="margin-left:auto;"><?= count($pscs) ?> total</span>
    </div>
    <ul class="people-list">
      <?php foreach ($pscs as $psc): ?>
      <li class="person-row" style="padding:var(--space-4); border-bottom:1px solid var(--outline-variant);">
        <span class="material-symbols-outlined person-icon">shield_person</span>
        <div>
          <p class="person-name"><?= $psc['name'] ?></p>
          <p class="person-role"><?= $psc['nature'] ?></p>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>


  <?php elseif ($section === 'address'): ?>
  <div class="card office-card card-flush" style="max-width:480px;">
    <div class="office-hero">
      <span class="material-symbols-outlined office-hero-icon">location_city</span>
    </div>
    <div style="padding:var(--space-6);">
      <div class="card-title-row">
        <span class="material-symbols-outlined card-icon">location_on</span>
        <h2 class="card-title card-title-inline">Registered Office Address</h2>
      </div>
      <address class="office-address" style="font-size:var(--text-body-lg); line-height:2;">
        <?php if ($company['address_line1']): ?><?= htmlspecialchars($company['address_line1']) ?><br><?php endif; ?>
        <?php if ($company['address_line2']): ?><?= htmlspecialchars($company['address_line2']) ?><br><?php endif; ?>
        <?= htmlspecialchars($company['address_town']) ?><br>
        <?= htmlspecialchars($company['address_post']) ?><br>
        <?= htmlspecialchars($company['address_country']) ?>
      </address>
      <a href="https://maps.google.com/?q=<?= urlencode($company['address_line1'].', '.$company['address_town'].', '.$company['address_post']) ?>"
         target="_blank" rel="noopener noreferrer"
         class="btn btn-secondary" style="margin-top:var(--space-6); display:inline-flex;">
        <span class="material-symbols-outlined" style="font-size:18px;">map</span>
        View on Google Maps
      </a>
    </div>
  </div>

  <?php endif; ?>


</div><!-- /.company-content -->
</main>

<!-- Footer: no inline margin — mobile.css resets it -->
<footer class="footer company-footer">
  <span class="footer-copy">AAA WEB-FILING</span>
  <span>&copy; <?= date('Y') ?> Companies House &nbsp;·&nbsp;
    <a href="/chadmin/views/theme/layout/privacy.php" style="color:var(--on-surface-variant);">Privacy</a>
    &nbsp;·&nbsp;
    <a href="/chadmin/views/theme/layout/terms.php" style="color:var(--on-surface-variant);">Terms</a>
  </span>
</footer>

<script src="/chadmin/views/theme/assets/js/script.js" defer></script>
</body>
</html>