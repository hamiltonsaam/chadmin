<div style="margin-top:20px;">
    <h3>Active directors and secretary</h3>
    <?php if (!$activeOfficers): ?>
        <div class="muted">No active directors/secretary stored yet.</div>
    <?php else: ?>
        <ul>
            <?php foreach ($activeOfficers as $officer): ?>
                <li><?= h($officer['name'] . ' (' . $officer['role'] . ')') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<div style="margin-top:20px;">
    <h3>Active PSCs</h3>
    <?php if (!$activePscs): ?>
        <div class="muted">No active PSCs stored yet.</div>
    <?php else: ?>
        <ul>
            <?php foreach ($activePscs as $psc): ?>
                <li><?= h($psc['name']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>