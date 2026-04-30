<?php
$categories    = $categories   ?? [];
$todoCategory  = $todoCategory ?? '';
$searchType    = $searchType   ?? '';
$searchQuery   = $searchQuery  ?? '';
$todoCompanies = $todoCompanies ?? [];

// Build flat list of individual todo items from both due dates
$todoItems = [];
foreach ($todoCompanies as $co) {
    $num  = (string) ($co['company_number'] ?? '');
    $name = (string) ($co['company_name']   ?? $co['label'] ?? $num);
    $cat  = (string) ($co['category']       ?? '');

    $dates = [
        'Accounts'               => (string) ($co['accounts_due_date']                ?? ''),
        'Confirmation Statement' => (string) ($co['confirmation_statement_due_date']  ?? ''),
    ];

    foreach ($dates as $type => $dueDate) {
        if ($dueDate === '') continue;
        $due = due_status($dueDate);
        if ($due['status'] === 'ok') continue; // only show overdue / due_soon
        $todoItems[] = [
            'num'      => $num,
            'name'     => $name,
            'category' => $cat,
            'type'     => $type,
            'due_date' => $dueDate,
            'due'      => $due,
        ];
    }
}

// Sort: overdue first, then by days ascending
usort($todoItems, function($a, $b) {
    if ($a['due']['status'] !== $b['due']['status']) {
        return $a['due']['status'] === 'overdue' ? -1 : 1;
    }
    return $a['due']['days'] <=> $b['due']['days'];
});

$overdueCount = count(array_filter($todoItems, fn($i) => $i['due']['status'] === 'overdue'));
$dueSoonCount = count(array_filter($todoItems, fn($i) => $i['due']['status'] === 'due_soon'));
$totalActions = $overdueCount + $dueSoonCount;
?>

<div class="card" style="padding:0;overflow:hidden;">

  <!-- ── Header ───────────────────────────────────────────── -->
  <div style="padding:var(--space-4) var(--space-6);border-bottom:1px solid var(--outline-variant);display:flex;align-items:center;gap:var(--space-3);">
    <span class="material-symbols-outlined card-icon">checklist</span>
    <h2 class="card-title" style="margin:0;">To-Do List</h2>
    <span class="badge <?= $totalActions > 0 ? 'badge-warning' : 'badge-active' ?>" style="margin-left:auto;">
      <?= $totalActions > 0
          ? $totalActions . ' action' . ($totalActions !== 1 ? 's' : '') . ' needed'
          : 'All clear' ?>
    </span>
  </div>

  <!-- ── Overdue / Due Soon counts ────────────────────────── -->
  <?php if ($totalActions > 0): ?>
  <div style="display:flex;border-bottom:1px solid var(--outline-variant);">
    <div style="flex:1;padding:var(--space-3) var(--space-6);border-right:1px solid var(--outline-variant);display:flex;align-items:center;gap:var(--space-2);">
      <span style="width:8px;height:8px;border-radius:50%;background:var(--error);display:inline-block;flex-shrink:0;"></span>
      <span style="font-size:var(--text-body-sm);color:var(--error);font-weight:700;"><?= $overdueCount ?> Overdue</span>
    </div>
    <div style="flex:1;padding:var(--space-3) var(--space-6);display:flex;align-items:center;gap:var(--space-2);">
      <span style="width:8px;height:8px;border-radius:50%;background:#d97706;display:inline-block;flex-shrink:0;"></span>
      <span style="font-size:var(--text-body-sm);color:#b45309;font-weight:700;"><?= $dueSoonCount ?> Due Soon</span>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Category filter ──────────────────────────────────── -->
  <?php if (count($categories) > 0): ?>
  <div style="padding:var(--space-3) var(--space-6);border-bottom:1px solid var(--outline-variant);background:var(--surface-container-low);">
    <form method="get" action="index.php" style="display:flex;align-items:center;gap:var(--space-2);flex-wrap:wrap;">
      <input type="hidden" name="search_type" value="<?= h($searchType) ?>">
      <input type="hidden" name="q"           value="<?= h($searchQuery) ?>">
	  <input type="hidden" name="todo_page" value="1">
      <div class="select-wrap" style="min-width:180px;">
        <select name="todo_category" class="select" style="font-size:var(--text-body-sm);" onchange="this.form.submit()">
          <option value="">All categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= h($cat) ?>" <?= $todoCategory === $cat ? 'selected' : '' ?>>
            <?= h($cat) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <span class="material-symbols-outlined select-arrow">expand_more</span>
      </div>
      <?php if ($todoCategory !== ''): ?>
	  
        <a href="index.php" class="btn btn-sm btn-secondary">Clear</a>
      <?php endif; ?>
    </form>
  </div>
  <?php endif; ?>

  <!-- ── Todo items ───────────────────────────────────────── -->
  <?php if (!empty($todoItems)): ?>
  <ul style="list-style:none;padding:var(--space-3) var(--space-4);display:flex;flex-direction:column;gap:var(--space-2);">

    <?php foreach ($todoItems as $item):
      $due = $item['due'];

      if ($due['status'] === 'overdue') {
        $dotBg      = 'var(--error)';
        $labelColor = 'var(--error)';
        $rowBg      = 'rgba(186,26,26,0.04)';
        $rowBorder  = 'rgba(186,26,26,0.18)';
        $tagBg      = '#ffdad6';
        $tagColor   = '#93000a';
      } else {
        $dotBg      = '#d97706';
        $labelColor = '#b45309';
        $rowBg      = 'rgba(217,119,6,0.04)';
        $rowBorder  = 'rgba(217,119,6,0.18)';
        $tagBg      = '#fff7ed';
        $tagColor   = '#b45309';
      }
    ?>
    <li style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-3) var(--space-4);border-radius:var(--radius-md);background:<?= $rowBg ?>;border:1px solid <?= $rowBorder ?>;">

      <span style="width:10px;height:10px;border-radius:50%;background:<?= $dotBg ?>;flex-shrink:0;"></span>

      <div style="flex:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:var(--space-2);flex-wrap:wrap;margin-bottom:3px;">
          <span style="font-size:11px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:<?= $labelColor ?>;">
            <?= h($item['type']) ?>
          </span>
          <span style="font-size:10px;font-weight:600;padding:1px 6px;border-radius:3px;background:<?= $tagBg ?>;color:<?= $tagColor ?>;">
            <?= h($due['tag']) ?>
          </span>
        </div>
        <a href="company_details.php?company=<?= urlencode($item['num']) ?>" class="inline-link" style="font-size:var(--text-body-sm);font-weight:600;word-break:break-word;">
		  <?= h($item['name']) ?>
		</a>
        <span style="color:var(--on-surface-variant);font-size:var(--text-body-sm);word-break:break-word;"> · <?= h($item['num']) ?></span>
      </div>

      
    </li>
    <?php endforeach; ?>
  </ul>

  <?php if (($todoPages ?? 1) > 1): ?>
    <div style="padding:var(--space-3) var(--space-6);border-top:1px solid var(--outline-variant);display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
      <?php for ($i = 1; $i <= $todoPages; $i++): ?>
        <a
          class="btn btn-sm <?= $i === ($todoPage ?? 1) ? 'btn-primary' : 'btn-secondary' ?>"
          href="index.php?todo_page=<?= $i ?><?= $todoCategory !== '' ? '&todo_category=' . urlencode($todoCategory) : '' ?>"
        >
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>

  <?php else: ?>
  <div style="padding:var(--space-10);text-align:center;color:var(--on-surface-variant);">
    <span class="material-symbols-outlined" style="font-size:40px;display:block;margin-bottom:var(--space-3);opacity:0.35;">task_alt</span>
    <p style="font-size:var(--text-body-sm);font-weight:600;color:var(--on-surface);margin-bottom:var(--space-1);">All up to date</p>
    <p style="font-size:var(--text-body-sm);">No companies require action right now.</p>
  </div>
  <?php endif; ?>
  <!-- ── Footer link ──────────────────────────────────────── -->
  <div style="padding:var(--space-3) var(--space-6);border-top:1px solid var(--outline-variant);text-align:center;">
    <a href="company_details.php?view=companies" class="inline-link" style="font-size:var(--text-body-sm);font-weight:600;">
      View all companies →
    </a>
  </div>

</div>