<?php
$activePage = 'dashboard';
$pageTitle  = 'Dashboard';
require __DIR__ . '/theme/layout/header.php';
?>

<!-- Card Shows the numbers -->
<div class="stat-grid">

  <!-- ALWAYS SHOW -->
  <div class="stat-card">
    <p class="stat-label">Total Companies</p>
    <p class="stat-value"><?= (int) ($todoCounts['total'] ?? 0) ?></p>
  </div>

  <?php if (!empty($todoCounts['red'])): ?>
  <div class="stat-card">
    <p class="stat-label">Overdue</p>
    <p class="stat-value stat-err"><?= (int) $todoCounts['red'] ?></p>
  </div>
  <?php endif; ?>

  <?php if (!empty($todoCounts['due_soon'])): ?>
  <div class="stat-card">
    <p class="stat-label">Due Soon</p>
    <p class="stat-value stat-warn"><?= (int) $todoCounts['due_soon'] ?></p>
  </div>
  <?php endif; ?>

  <?php if (!empty($todoCounts['ok'])): ?>
  <div class="stat-card">
    <p class="stat-label">Up to Date</p>
    <p class="stat-value stat-up"><?= (int) $todoCounts['ok'] ?></p>
  </div>
  <?php endif; ?>

  <?php if (!empty($todoCounts['brown'])): ?>
  <div class="stat-card">
    <p class="stat-label">Dissolved</p>
    <p class="stat-value stat-err"><?= (int) $todoCounts['brown'] ?></p>
  </div>
  <?php endif; ?>

  <?php if (!empty($todoCounts['accounts'])): ?>
  <div class="stat-card">
    <p class="stat-label">Accounts Due</p>
    <p class="stat-value stat-warn"><?= (int) $todoCounts['accounts'] ?></p>
  </div>
  <?php endif; ?>

  <?php if (!empty($todoCounts['confirmation'])): ?>
  <div class="stat-card">
    <p class="stat-label">Confirmation Due</p>
    <p class="stat-value stat-warn"><?= (int) $todoCounts['confirmation'] ?></p>
  </div>
  <?php endif; ?>

  <?php if (!empty($totalArchived)): ?>
  <div class="stat-card">
    <p class="stat-label">Total Archived</p>
    <p class="stat-value stat-warn"><?= (int) $totalArchived ?></p>
  </div>
  <?php endif; ?>

</div>

<!-- Add company strip -->
<div class="card" style="padding:var(--space-4) var(--space-6);">
  <div style="display:flex;align-items:center;gap:var(--space-4);flex-wrap:wrap;">
    <span class="material-symbols-outlined" style="color:var(--primary-container);font-size:28px;">add_business</span>
    <div style="flex:1;min-width:180px;">
      <p style="font-weight:600;color:var(--on-surface);margin:0;">Add a company to your portfolio</p>
      <p style="font-size:var(--text-body-sm);color:var(--on-surface-variant);margin:0;">Enter details manually or search via Companies House.</p>
    </div>
    <div style="display:flex;gap:var(--space-3);">
		<button class="btn btn-secondary" onclick="togglePanel('manual')">
		  <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
		  Add Manually
		</button>
		  <button class="btn btn-primary" onclick="togglePanel('search')">
		  <span class="material-symbols-outlined" style="font-size:18px;">search</span>
		  Search Companies House
		</button>
    </div>
  </div>

  <!-- Inline add panel (hidden by default) -->
  <div id="panel-add-manual" class="hidden" style="margin-top:var(--space-4);padding-top:var(--space-4);border-top:1px solid var(--outline-variant);">
    <?php require __DIR__ . '/partials/add_company_panel.php'; ?>
  </div>

  <!-- Inline search panel (hidden by default) -->
  <div id="panel-search" class="hidden" style="margin-top:var(--space-4);padding-top:var(--space-4);border-top:1px solid var(--outline-variant);">
    <?php require __DIR__ . '/partials/search_companies_panel.php'; ?>
  </div>
</div>

<!-- Two-column grid -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6);align-items:start;">
  <div>
    <?php require __DIR__ . '/partials/todo_companies_panel.php'; ?>
  </div>
  <div>
    <?php require __DIR__ . '/partials/companies_list_table.php'; ?>
  </div>
</div>

<style>.hidden{display:none!important;}</style>

<?php require __DIR__ . '/theme/layout/footer.php'; ?>

<script>
function togglePanel(type) {
  const manual = document.getElementById('panel-add-manual');
  const search = document.getElementById('panel-search');

  if (type === 'manual') {
    manual.classList.toggle('hidden');
    search.classList.add('hidden'); // ALWAYS close other
  }

  if (type === 'search') {
    search.classList.toggle('hidden');
    manual.classList.add('hidden'); // ALWAYS close other
  }
}
</script>