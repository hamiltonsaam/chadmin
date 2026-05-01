<?php
declare(strict_types=1);

// ── Guard: ensure required vars exist ──────────────────
if (!isset($pageTitle))  $pageTitle  = 'AAA WEB-FILING';
if (!isset($activeLink)) $activeLink = '';

// ── Session user vars ───────────────────────────────────
$userId  = $_SESSION['user_id']  ?? '';
$isAdmin = !empty($_SESSION['is_admin']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?> — AAA WEB-FILING</title>

  <!-- Google Fonts: Public Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet" />

  <!-- Material Symbols -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

  <!-- Stylesheet -->
  <link rel="stylesheet" href="/chadmin/views/theme/assets/css/styles.css?v=3.2" />
  <style>
    body.company-page {
      display: block !important;
      overflow-x: hidden !important;
    }

    button[disabled] {
    opacity: 0.55;
    cursor: not-allowed;
    }
  </style>

</head>
<body class="company-page">
<!--


 *******ATTENTION**********************************
  ONLY FOR COMPANY DETAILS PAGE- FILING PAGE VIEW USES A DIFFERENT HEADER:
  PLACE TOP of the page 
  php: include __DIR__ . '/theme/layout/topbar.php'; 


-->

<?php include __DIR__ . '/theme/layout/topbar.php'; ?>


  
<!-- ═══════════════════════════════════════
     COMPANY SECONDARY SIDEBAR
═══════════════════════════════════════ -->

<nav class="company-sidebar" aria-label="Company navigation">

  <div class="company-sidebar-top">
  <!-- Brand -->
    <div class="brand-logo">
    <img src="/chadmin/views/theme/layout/logo.png" alt="A1A eFiling" class="brand-img">
    </div>
    
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
      $url = nav_url($key, $companyNumber);
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
    <a href="companies_list.php" class="company-sidebar-link">
      <span class="material-symbols-outlined">arrow_back</span>
      All Companies
    </a>
  </div>

</nav>

<!-- ═══════════════════════════════════════
     END OF SECONDARY SIDEBAR
     START OF MAIN CONTENT
     (main tag opened in footer.php, closed in footer.php)
═══════════════════════════════════════ -->         


<!-- ═══════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════ -->
<main class="company-main">
<div class="company-content">


  <!-- Company Header -->
  <div class="company-header">
    <div class="company-header-row">
      <div>
        <h1 class="company-title" style="color: #000785;"><?= htmlspecialchars($pageTitle) ?></h1>
        <p class="company-subtitle">
          Company number: <strong><?= htmlspecialchars($companyNumber) ?></strong>
        </p>
      </div>
      <span class="company-status">
        <span class="material-symbols-outlined">
          <?= $companyStatus === 'active' ? 'check_circle' : 'cancel' ?>
        </span>
        <?= htmlspecialchars($companyStatus) ?>
      </span>
    </div>

    <!-- Action buttons -->
    <div class="company-header-row"
         style="margin-top:var(--space-4); flex-wrap:wrap; gap:var(--space-2);">
      <a class="btn gray"
         href="index.php?action=archive&company=<?= urlencode($companyNumber) ?>"
         onclick="return confirm('Archive this company?')">
        <span class="material-symbols-outlined" style="font-size:18px; color: #ac7f0dca;">archive</span>
        Archive
      </a>
      <a class="btn red"
         href="index.php?action=delete&company=<?= urlencode($companyNumber) ?>"
         onclick="return confirm('Are you sure you want to delete it?')">
        <span class="material-symbols-outlined" style="font-size:18px; color: #ff0000;">delete</span>
        Delete
      </a>
      <a class="btn"
         href="filing_page.php?company=<?= urlencode($companyNumber) ?>"
         target="_parent" rel="noopener noreferrer">
        <span class="material-symbols-outlined" style="font-size:18px;">open_in_new</span>
        Web Filing
      </a>
      <a class="btn"
         href="software_filing_test.php?company=<?= urlencode($companyNumber) ?>"
         target="_parent" rel="noopener noreferrer">
        <span class="material-symbols-outlined" style="font-size:18px; color: #aa00de">bug_report</span>
        Software Filing
      </a>
      <a class="btn light"
         href="<?= h(companies_house_company_url($companyNumber)) ?>"
         target="_blank" rel="noopener noreferrer">
        <span class="material-symbols-outlined" style="font-size:18px; color: #0070fa;">language</span>
        Open on Companies House
      </a>
      <a class="btn"
         href="index.php?action=sync&company=<?= urlencode($companyNumber) ?>">
        <span class="material-symbols-outlined" style="font-size:18px; color: #3d9e00;">sync</span>
        Sync Now
      </a>
    </div>
  </div>

		<?php
		// ── Highlight-box colours + tag for Accounts & Confirmation Statement ──
		$accountsDue  = ($accountsNextDue  ?? '') !== '' ? due_status($accountsNextDue)  : null;
		$statementDue = ($statementNextDue ?? '') !== '' ? due_status($statementNextDue) : null;

		$resolveHighlight = function(?array $due): array {
			if ($due === null || $due['status'] === 'ok') {
				return ['style' => '', 'tag' => '', 'tagBg' => '', 'tagColor' => '', 'dateColor' => ''];
			}
			if ($due['status'] === 'overdue') {
				return [
					'style'      => 'background:#ffdad6; border-color:rgba(186,26,26,0.25);',
					'tag'        => $due['tag'],
					'tagBg'      => '#ffdad6',
					'tagColor'   => '#93000a',
					'dateColor'  => '#93000a',
				];
			}
			// due_soon
			return [
				'style'      => 'background:#fff7ed; border-color:rgba(217,119,6,0.25);',
				'tag'        => $due['tag'],
				'tagBg'      => '#fff7ed',
				'tagColor'   => '#b45309',
				'dateColor'  => '#b45309',
			];
		};

		$aH = $resolveHighlight($accountsDue);
		$sH = $resolveHighlight($statementDue);
		?>

  <?php if ($section === 'overview'): ?>
  <!-- ═══════ OVERVIEW ═══════ -->
  <div class="company-layout">

    <div class="company-primary">

      <!-- Company Details -->
      <div class="card">
        <div class="card-title-row">
          <span class="material-symbols-outlined card-icon">info</span>
          <h2 class="card-title card-title-inline">Company Details</h2>
        </div>
        <div class="detail-grid">
          <div>
            <p class="meta-label">Company Type</p>
            <p class="meta-value"><?= h($profile['type'] ?? '') ?></p>
          </div>
          <div>
            <p class="meta-label">Incorporated</p>
            <p class="meta-value"><?= h($profile['date_of_creation'] ?? '') ?></p>
          </div>
          <div>
            <p class="meta-label">Status</p>
            <p class="meta-value">
              <span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($companyStatus) ?></span>
            </p>
          </div>
          <div>
            <p class="meta-label">SIC Code</p>
            <p class="meta-value"><?= h(implode(', ', $profile['sic_codes'] ?? [])) ?></p>
          </div>
          <div>
            <p class="meta-label">Category</p>
            <p class="meta-value"><?= h($selectedCompany['category'] ?? '—') ?></p>
          </div>
        </div>
      </div>

      <!-- Accounts + Confirmation Statement -->
      <div class="panels-grid">

        <div class="card">
          <div class="card-title-row">
            <span class="material-symbols-outlined card-icon">receipt_long</span>
            <h2 class="card-title card-title-inline">Accounts</h2>
          </div>
          <div class="highlight-box" <?= $aH['style'] ? 'style="' . $aH['style'] . '"' : '' ?>>
				<p class="highlight-date" <?= $aH['dateColor'] ? 'style="color:' . $aH['dateColor'] . ';font-weight:700;"' : '' ?>>
					<?= h($accountsNextDue) ?>
				</p>
				<p class="highlight-note highlight-note-spaced">Next accounts due</p>
				<?php if ($aH['tag'] !== ''): ?>
				<div style="margin-top:var(--space-2);display:flex;align-items:center;gap:var(--space-2);">
					<span style="width:7px;height:7px;border-radius:50%;background:<?= $aH['tagColor'] ?>;display:inline-block;flex-shrink:0;"></span>
					<span style="font-size:11px;font-weight:700;letter-spacing:0.04em;color:<?= $aH['tagColor'] ?>;">
						<?= h($aH['tag']) ?>
					</span>
				</div>
				<?php endif; ?>
				<p class="meta-label" style="margin-top:var(--space-2);">Made up to</p>
				<p class="meta-value text-strong"><?= h($profile['accounts']['next_accounts']['period_end_on'] ?? '') ?></p>
			</div>
          <p class="meta-label">Last accounts</p>
          <p class="meta-value"><?= h($accountsLastMadeUpTo) ?></p>
        </div>

        <div class="card">
          <div class="card-title-row">
            <span class="material-symbols-outlined card-icon">task_alt</span>
            <h2 class="card-title card-title-inline">Confirmation Statement</h2>
          </div>
          <div class="highlight-box" <?= $sH['style'] ? 'style="' . $sH['style'] . '"' : '' ?>>
				<p class="highlight-date" <?= $sH['dateColor'] ? 'style="color:' . $sH['dateColor'] . ';font-weight:700;"' : '' ?>>
					<?= h($statementNextDue) ?>
				</p>
				<p class="highlight-note highlight-note-spaced">Next statement due</p>
				<?php if ($sH['tag'] !== ''): ?>
				<div style="margin-top:var(--space-2);display:flex;align-items:center;gap:var(--space-2);">
					<span style="width:7px;height:7px;border-radius:50%;background:<?= $sH['tagColor'] ?>;display:inline-block;flex-shrink:0;"></span>
					<span style="font-size:11px;font-weight:700;letter-spacing:0.04em;color:<?= $sH['tagColor'] ?>;">
						<?= h($sH['tag']) ?>
					</span>
				</div>
				<?php endif; ?>
				<p class="meta-label" style="margin-top:var(--space-2);">Made up to</p>
				<p class="meta-value text-strong"><?= h($statementLastMadeUpTo) ?></p>
		</div>
          <p class="meta-label">Last statement</p>
          <p class="meta-value"><?= h($statementLastMadeUpTo) ?></p>
        </div>

      </div>

      <!-- Recent Filings -->
	  <?php require __DIR__ . '/partials/company_details_filings.php'; ?>
      <div class="card card-flush">
        <div class="card-flush-header card-title-row">
          <span class="material-symbols-outlined card-icon">folder_open</span>
          <h2 class="card-title card-title-inline">Recent Filings</h2>
          <a href="<?= nav_url('filings', $companyNumber) ?>"
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
              <?php if (empty($selectedFilings)): ?>
              <tr>
                <td colspan="4" class="td-muted" style="padding:var(--space-6); text-align:center;">
                  No filings found.
                </td>
              </tr>
              <?php else: ?>
              <?php foreach (array_slice($selectedFilings, 0, 3) as $filing): ?>
              <tr>
                <td class="td-muted td-nowrap"><?= h($filing['date'] ?? '') ?></td>
                <td><?= h($filing['description'] ?? '') ?></td>
                <td><span class="badge badge-neutral"><?= h($filing['form_type'] ?? '') ?></span></td>
                <td class="td-right">
                  <a href="#" class="inline-link">
                    <span class="material-symbols-outlined icon-sm">download</span>
                    Download
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /.company-primary -->


    <div class="company-secondary">

      <!-- Registered Address -->
      <?php
        $addrParts = $profile['registered_office_address'] ?? [];
        $addrLine1 = $addrParts['address_line_1'] ?? '';
        $addrLine2 = $addrParts['address_line_2'] ?? '';
        $addrTown  = $addrParts['locality'] ?? '';
        $addrPost  = $addrParts['postal_code'] ?? '';
        $addrCountry = $addrParts['country'] ?? '';
        $mapsQuery = urlencode("$addrLine1, $addrTown, $addrPost");
      ?>
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
            <?php if ($addrLine1): ?><?= h($addrLine1) ?><br><?php endif; ?>
            <?php if ($addrLine2): ?><?= h($addrLine2) ?><br><?php endif; ?>
            <?php if ($addrTown): ?><?= h($addrTown) ?><br><?php endif; ?>
            <?php if ($addrPost): ?><?= h($addrPost) ?><br><?php endif; ?>
            <?php if ($addrCountry): ?><?= h($addrCountry) ?><?php endif; ?>
          </address>
          <a href="https://maps.google.com/?q=<?= $mapsQuery ?>"
             target="_blank" rel="noopener noreferrer"
             class="inline-link" style="margin-top:var(--space-4);display:inline-flex;">
            View on map
            <span class="material-symbols-outlined icon-sm">open_in_new</span>
          </a>
        </div>
      </div>

      <!-- Officers -->
      <div class="card">
        <div class="card-title-row">
          <span class="material-symbols-outlined card-icon">manage_accounts</span>
          <h2 class="card-title card-title-inline">Officers</h2>
          <span class="badge badge-neutral" style="margin-left:auto;"><?= count($activeOfficers) ?></span>
        </div>
        <?php if (empty($activeOfficers)): ?>
          <p class="meta-label" style="padding:var(--space-4) 0;">No active officers found.</p>
        <?php else: ?>
          <ul class="people-list">
            <?php foreach ($activeOfficers as $officer): ?>
            <li class="person-row">
              <span class="material-symbols-outlined person-icon">person</span>
              <div>
                <p class="person-name"><?= h($officer['name'] ?? '') ?></p>
                <p class="person-role">
                  <?= h($officer['officer_role'] ?? '') ?>
                  — appointed <?= h($officer['appointed_on'] ?? '') ?>
                </p>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
          <div style="margin-top:var(--space-3);">
            <a href="<?= nav_url('officers', $companyNumber) ?>" class="inline-link">
              View all officers
              <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- PSC -->
      <div class="card">
        <div class="card-title-row">
          <span class="material-symbols-outlined card-icon">verified_user</span>
          <h2 class="card-title card-title-inline">PSC Register</h2>
          <span class="badge badge-neutral" style="margin-left:auto;"><?= count($activePscs) ?></span>
        </div>
        <?php if (empty($activePscs)): ?>
          <p class="meta-label" style="padding:var(--space-4) 0;">No PSCs on record.</p>
        <?php else: ?>
          <ul class="people-list">
            <?php foreach ($activePscs as $psc): ?>
            <li class="person-row">
              <span class="material-symbols-outlined person-icon">shield_person</span>
              <div>
                <p class="person-name"><?= h($psc['name'] ?? '') ?></p>
                <p class="person-role"><?= h($psc['natures_of_control'][0] ?? '') ?></p>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
          <div style="margin-top:var(--space-3);">
            <a href="<?= nav_url('psc', $companyNumber) ?>" class="inline-link">
              View full PSC register
              <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
            </a>
          </div>
        <?php endif; ?>
      </div>

    </div><!-- /.company-secondary -->

  </div><!-- /.company-layout -->


  <?php elseif ($section === 'filings'): ?>
  <!-- ═══════ FILINGS ═══════ -->
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
          <?php if (empty($selectedFilings)): ?>
          <tr>
            <td colspan="4" class="td-muted" style="padding:var(--space-6); text-align:center;">
              No filings found.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($selectedFilings as $i => $filing): ?>
          <tr class="<?= $i % 2 !== 0 ? 'row-alt' : '' ?>">
            <td class="td-muted td-nowrap"><?= h($filing['date'] ?? '') ?></td>
            <td><?= h($filing['description'] ?? '') ?></td>
            <td><span class="badge badge-neutral"><?= h($filing['form_type'] ?? '') ?></span></td>
            <td class="td-right">
              <a href="#" class="inline-link">
                <span class="material-symbols-outlined icon-sm">download</span>
                Download
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>


  <?php elseif ($section === 'officers'): ?>
  <!-- ═══════ OFFICERS ═══════ -->
  <div class="card">
    <div class="card-title-row">
      <span class="material-symbols-outlined card-icon">manage_accounts</span>
      <h2 class="card-title card-title-inline">Officers</h2>
      <span class="badge badge-neutral" style="margin-left:auto;"><?= count($activeOfficers) ?> total</span>
    </div>
    <?php if (empty($activeOfficers)): ?>
      <p class="meta-label" style="padding:var(--space-6);">No active officers found.</p>
    <?php else: ?>
      <ul class="people-list">
        <?php foreach ($activeOfficers as $officer): ?>
        <li class="person-row"
            style="padding:var(--space-4); border-bottom:1px solid var(--outline-variant);">
          <span class="material-symbols-outlined person-icon">person</span>
          <div style="flex:1;">
            <p class="person-name"><?= h($officer['name'] ?? '') ?></p>
            <p class="person-role">
              <?= h($officer['officer_role'] ?? '') ?>
              &nbsp;·&nbsp; Appointed <?= h($officer['appointed_on'] ?? '') ?>
            </p>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>


  <?php elseif ($section === 'psc'): ?>
  <!-- ═══════ PSC ═══════ -->
  <div class="card">
    <div class="card-title-row">
      <span class="material-symbols-outlined card-icon">verified_user</span>
      <h2 class="card-title card-title-inline">Persons with Significant Control</h2>
      <span class="badge badge-neutral" style="margin-left:auto;"><?= count($activePscs) ?> total</span>
    </div>
    <?php if (empty($activePscs)): ?>
      <p class="meta-label" style="padding:var(--space-6);">No PSCs on record.</p>
    <?php else: ?>
      <ul class="people-list">
        <?php foreach ($activePscs as $psc): ?>
        <li class="person-row"
            style="padding:var(--space-4); border-bottom:1px solid var(--outline-variant);">
          <span class="material-symbols-outlined person-icon">shield_person</span>
          <div>
            <p class="person-name"><?= h($psc['name'] ?? '') ?></p>
            <p class="person-role"><?= h($psc['natures_of_control'][0] ?? '') ?></p>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>


  <?php elseif ($section === 'address'): ?>
  <!-- ═══════ ADDRESS ═══════ -->
  <!-- Registered Address -->
      <?php
        $addrParts = $profile['registered_office_address'] ?? [];
        $addrLine1 = $addrParts['address_line_1'] ?? '';
        $addrLine2 = $addrParts['address_line_2'] ?? '';
        $addrTown  = $addrParts['locality'] ?? '';
        $addrPost  = $addrParts['postal_code'] ?? '';
        $addrCountry = $addrParts['country'] ?? '';
        $mapsQuery = urlencode("$addrLine1, $addrTown, $addrPost");
      ?>
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
            <?php if ($addrLine1): ?><?= h($addrLine1) ?><br><?php endif; ?>
            <?php if ($addrLine2): ?><?= h($addrLine2) ?><br><?php endif; ?>
            <?php if ($addrTown): ?><?= h($addrTown) ?><br><?php endif; ?>
            <?php if ($addrPost): ?><?= h($addrPost) ?><br><?php endif; ?>
            <?php if ($addrCountry): ?><?= h($addrCountry) ?><?php endif; ?>
      </address>
      <a href="https://maps.google.com/?q=<?= $mapsQuery ?>"
         target="_blank" rel="noopener noreferrer"
         class="btn btn-secondary" style="margin-top:var(--space-6); display:inline-flex;">
        <span class="material-symbols-outlined" style="font-size:18px;">map</span>
        View on Google Maps
      </a>
    </div>
  </div>

  <?php endif; ?>

<!-- ═══════════════════════════════════════
     FOOTER  --- this includes the closing tags for 
        </div>
      </main> 
   </body>
</html>        ----   Also js Codes
════════════════════════════════════════ -->
    <?php require __DIR__ . '/theme/layout/footer.php'; ?>
       