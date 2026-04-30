<div class="card">
    <h2 style="margin-top:0;">Add Company</h2>
    <form method="post" action="index.php?action=add_company" class="login-form">
        <div class="modal-body">
         <div class="form-grid">
            <div class="form-group span-2">       
				<label class="form-label">
				Company Number
				<input type="text" name="company_number" class="input" placeholder="01234567" required>
				</label>
			</div>
			<div class="form-group span-2">
				<label class="form-label">
				Your label
				<input type="text" class="input" name="label" placeholder="Client name">
				</label>
			</div>
			<div class="form-group span-2">
				<label class="form-label">
				Existing Category
				<select class="select" name="category_existing">
                <option value="">Select Category</option>
                <?php foreach ($categories as $categoryItem): ?>
                    <option value="<?= h($categoryItem) ?>"><?= h($categoryItem) ?></option>
                <?php endforeach; ?>
				</select>
				</label >
				<label class="form-label">
				Or Add New Category
				<input type="text" class="input" name="category_new" placeholder="client / my companies / supplier">
				</label>
			</div>
				<button type="submit" class="btn btn-primary">Add company</button>
			</div>
	</div>
   </form>
</div>