<div class="card">
    <h2 style="margin-top:0;">Add company manually</h2>
    <form method="post" action="index.php?action=add_company">
        <label>
            Company number
            <input type="text" name="company_number" placeholder="01234567" required>
        </label>
        <label>
            Your label
            <input type="text" name="label" placeholder="Client name">
        </label>
        <label>
            Existing category
            <select name="category_existing">
                <option value="">Select category</option>
                <?php foreach ($categories as $categoryItem): ?>
                    <option value="<?= h($categoryItem) ?>"><?= h($categoryItem) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Or new category
            <input type="text" name="category_new" placeholder="client / my companies / supplier">
        </label>
        <button type="submit">Add company</button>
    </form>
</div>