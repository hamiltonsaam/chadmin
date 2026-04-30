<?php
$pageTitle  = 'My Dashboard';
$activeLink = 'user-dashboard.php';
$userId     = $_SESSION['user_id'] ?? 'CH-99210';

include __DIR__ . '/header.php';
?>
<?php ob_start(); ?>

<!-- PAGE HEADER + ADD COMPANY BUTTON -->
<div class="page-header-row">

  <div class="page-header" style="margin-bottom:0;">
    <h1>Welcome back, <?= htmlspecialchars($userId) ?></h1>
    <p>Manage your company filings and administrative tasks below.</p>
  </div>

  <button class="btn btn-primary" data-open-modal="modal-add-choice" style="flex-shrink:0; margin-top: var(--space-2);">
    <span class="material-symbols-outlined" style="font-size:18px;">add_business</span>
    Add a company to your portfolio
  </button>

</div>


<!-- TWO-COLUMN LAYOUT: Stats+Todo LEFT | Quick Actions RIGHT -->
<div class="dashboard-grid">

  <!-- LEFT COLUMN -->
  <div style="display:flex; flex-direction:column; gap: var(--space-6);">

    <!-- Stat cards -->
    <div class="stat-grid">

      <div class="stat-card">
        <div class="stat-label">Companies</div>
        <div class="stat-value"><?= number_format($totalCompanies ?? 42) ?></div>
        <div class="stat-meta">In your portfolio</div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Overdue</div>
        <div class="stat-value stat-err"><?= number_format($overdueCount ?? 1) ?></div>
        <div class="stat-meta stat-err">Immediate action needed</div>
      </div>

      <div class="stat-card">
        <div class="stat-label">Due Soon</div>
        <div class="stat-value stat-warn"><?= number_format($dueSoonCount ?? 2) ?></div>
        <div class="stat-meta stat-warn">Within 30 days</div>
      </div>

      <div class="stat-card">
        <div class="stat-label">All Clear</div>
        <div class="stat-value stat-up"><?= number_format($allClearCount ?? 39) ?></div>
        <div class="stat-meta stat-up">No action required</div>
      </div>

    </div>


    <!-- To-Do List -->
    <div class="card">

      <div class="card-title-row">
        <span class="material-symbols-outlined card-icon">checklist</span>
        <h2 class="card-title" style="margin:0;">To-Do List</h2>
        <?php
          $totalTodos = count($todos ?? []) ?: 3;
        ?>
        <span class="badge badge-warning" style="margin-left:auto;"><?= $totalTodos ?> Action Required</span>
      </div>

      <ul class="todo-list">
        <?php
        $todos = $todos ?? [
          [
            'dot'         => 'todo-dot-red',
            'label'       => 'Confirmation Statement — OVERDUE',
            'label_class' => 'todo-label-overdue',
            'company'     => 'Acme Corp Ltd (01234567)',
            'href'        => 'company-details.php?id=01234567',
          ],
          [
            'dot'         => 'todo-dot-amber',
            'label'       => 'Annual Accounts — DUE IN 14 DAYS',
            'label_class' => 'todo-label-due',
            'company'     => 'Beta Holdings (09876543)',
            'href'        => 'company-details.php?id=09876543',
          ],
          [
            'dot'         => 'todo-dot-grey',
            'label'       => 'Update Director Details — DRAFT',
            'label_class' => 'todo-label-draft',
            'company'     => 'Gamma Industries (11223344)',
            'href'        => 'company-details.php?id=11223344',
          ],
        ];

        foreach ($todos as $todo): ?>
        <li class="todo-item">
          <span class="todo-dot <?= $todo['dot'] ?>"></span>
          <div style="flex:1; min-width:0;">
            <div class="todo-label <?= $todo['label_class'] ?>">
              <?= htmlspecialchars($todo['label']) ?>
            </div>
            <div class="todo-company">
              <?= htmlspecialchars($todo['company']) ?>
            </div>
          </div>
          <a href="<?= htmlspecialchars($todo['href']) ?>" class="btn btn-secondary btn-sm" style="flex-shrink:0;">
            View
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>

      <div class="inline-link-center">
        <a href="managed-companies.php" class="inline-link">
          View all companies
          <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
        </a>
      </div>

    </div><!-- /.card (todo) -->

  </div><!-- /.left column -->


  <!-- RIGHT COLUMN: Quick Actions -->
  <div style="display:flex; flex-direction:column; gap: var(--space-4);">

    <div class="card">
      <div class="card-title-row">
        <span class="material-symbols-outlined card-icon">bolt</span>
        <h2 class="card-title" style="margin:0;">Quick Actions</h2>
      </div>

      <div style="display:flex; flex-direction:column; gap: var(--space-2);">
        <a href="managed-companies.php" class="btn btn-secondary btn-full">
          <span class="material-symbols-outlined" style="font-size:18px;">domain</span>
          View All Companies
        </a>
        <a href="filings.php" class="btn btn-secondary btn-full">
          <span class="material-symbols-outlined" style="font-size:18px;">description</span>
          View Filings
        </a>
        <a href="deadlines.php" class="btn btn-secondary btn-full">
          <span class="material-symbols-outlined" style="font-size:18px;">event</span>
          Upcoming Deadlines
        </a>
        <button class="btn btn-primary btn-full" data-open-modal="modal-add-choice">
          <span class="material-symbols-outlined" style="font-size:18px;">add_business</span>
          Add New Company
        </button>
      </div>
    </div>

    <!-- Notice card -->
    <div class="card" style="background: var(--surface-container-low); border-color: var(--outline-variant);">
      <div style="display:flex; gap: var(--space-3); align-items:flex-start;">
        <span class="material-symbols-outlined" style="color: var(--primary-container); font-size:22px; flex-shrink:0; margin-top:2px;">info</span>
        <div>
          <p style="font-size: var(--text-body-sm); font-weight:600; color: var(--on-surface); margin-bottom: var(--space-1);">
            Filing reminder
          </p>
          <p style="font-size: var(--text-body-sm); color: var(--on-surface-variant); line-height:1.6;">
            Confirmation statements must be filed within 14 days of their due date to avoid penalties.
          </p>
        </div>
      </div>
    </div>

  </div><!-- /.right column -->

</div><!-- /.dashboard-grid -->


<!-- MODAL 1: Add Company Choice -->
<div class="modal-backdrop" id="modal-add-choice">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header">
      <h3 class="modal-title">Add a Company</h3>
      <button class="icon-btn" data-close-modal="modal-add-choice" aria-label="Close">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <div class="modal-body" style="display:flex; flex-direction:column; gap: var(--space-4);">
      <p style="font-size: var(--text-body-sm); color: var(--on-surface-variant);">
        Enter details manually or search via Companies House.
      </p>
      <button class="btn btn-primary btn-full" data-close-modal="modal-add-choice" data-open-modal="modal-search">
        <span class="material-symbols-outlined" style="font-size:18px;">search</span>
        Search Companies House
      </button>
      <button class="btn btn-secondary btn-full" data-close-modal="modal-add-choice" data-open-modal="modal-manual">
        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
        Enter Details Manually
      </button>
    </div>
  </div>
</div>


<!-- MODAL 2: Manual Entry -->
<div class="modal-backdrop" id="modal-manual">
  <div class="modal" style="max-width:480px;">
    <div class="modal-header">
      <h3 class="modal-title">Add Company Manually</h3>
      <button class="icon-btn" data-close-modal="modal-manual" aria-label="Close">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <div class="modal-body">
      <form id="form-manual" novalidate>
        <div class="form-grid" style="grid-template-columns:1fr;">

          <div class="form-group" style="display:flex; flex-direction:column; gap: var(--space-1);">
            <label class="form-label" for="m-name">Company Name</label>
            <input type="text" id="m-name" class="input" placeholder="e.g. Acme Global Solutions Ltd." />
          </div>

          <div class="form-group" style="display:flex; flex-direction:column; gap: var(--space-1);">
            <label class="form-label" for="m-number">Company Number</label>
            <input type="text" id="m-number" class="input" placeholder="e.g. 09876543" maxlength="8" />
          </div>

          <div class="form-group" style="display:flex; flex-direction:column; gap: var(--space-1);">
            <label class="form-label" for="m-type">Company Type</label>
            <div class="select-wrap">
              <select id="m-type" class="select">
                <option value="">Select type…</option>
                <option>Private Limited</option>
                <option>Public Limited</option>
                <option>LLP</option>
                <option>CIC</option>
                <option>Other</option>
              </select>
              <span class="material-symbols-outlined select-arrow">expand_more</span>
            </div>
          </div>

        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" data-close-modal="modal-manual">Cancel</button>
      <button class="btn btn-primary" id="btn-submit-manual">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span>
        Add Company
      </button>
    </div>
  </div>
</div>


<!-- MODAL 3: Companies House Search -->
<div class="modal-backdrop" id="modal-search">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Search Companies House</h3>
      <button class="icon-btn" data-close-modal="modal-search" aria-label="Close">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <div class="modal-body">

      <div class="search-bar">
        <div class="input-wrap" style="flex:1;">
          <span class="material-symbols-outlined input-icon">search</span>
          <input
            type="text"
            id="ch-search-input"
            class="input input-padded"
            placeholder="Search by company name or number…"
            autocomplete="off"
          />
        </div>
        <div class="select-wrap" style="width:140px;">
          <select id="ch-search-status" class="select">
            <option value="">All statuses</option>
            <option>Active</option>
            <option>Dissolved</option>
            <option>Liquidation</option>
            <option>Dormant</option>
          </select>
          <span class="material-symbols-outlined select-arrow">expand_more</span>
        </div>
      </div>

      <div class="search-results-table">
        <div class="table-scroll">
          <table class="data-table" id="ch-results-table" style="display:none;">
            <thead>
              <tr>
                <th>No.</th>
                <th>Company Name</th>
                <th>Type</th>
                <th class="th-center">Status</th>
                <th>Incorporated</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="ch-results-body"></tbody>
          </table>
        </div>
        <div id="ch-empty" class="result-empty" style="display:none;">
          No companies match your search.
        </div>
      </div>

    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" data-close-modal="modal-search">Close</button>
    </div>
  </div>
</div>


<?php include __DIR__ . '/footer.php'; ?>
<?php ob_end_flush(); ?>