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

<div style="margin-left: 220px !important; padding-left: 0px;">
    <?php include __DIR__ . '/theme/layout/topbar.php'; ?>
</div>