<form method="get" class="filters">
    <label>
        Category
        <select name="category">
            <option value="">All categories</option>
            <?php foreach ($categories as $item): ?>
                <option value="<?= h($item) ?>" <?= $category === $item ? 'selected' : '' ?>><?= h($item) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <input type="hidden" name="sort" value="<?= h($sort) ?>">
    <input type="hidden" name="dir" value="<?= h($dir) ?>">
    <input type="hidden" name="archived" value="<?= $archivedOnly ? '1' : '0' ?>">
    <button type="submit">Filter</button>
</form>