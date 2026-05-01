<?php
$activePage = 'profile';
$pageTitle  = 'My Profile';

require __DIR__ . '/theme/layout/header.php';
?>

<div class="profile-container">

    <div class="profile-hero card">
        <div class="profile-avatar">
    
</div>

        <div>
            <h1 class="profile-name">
                <?= profile_value($user, 'title') ?>
                <?= profile_value($user, 'first_name') ?>
                <?= profile_value($user, 'surname') ?>
            </h1>

            <p class="profile-subtitle">
                <?= profile_value($user, 'position') ?>
                <?= !empty($user['company_name']) ? ' at ' . profile_value($user, 'company_name') : '' ?>
            </p>

            <?php if (!empty($user['companies_house_id_verified'])): ?>
                <span class="badge badge-active">Companies House ID Verified</span>
            <?php else: ?>
                <span class="badge badge-warning">Companies House ID Not Verified</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="<?= (($flash['type'] ?? '') === 'error') ? 'flash-error' : 'flash-ok' ?>">
            <?= h((string) ($flash['message'] ?? '')) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="profile.php" class="card profile-form-card">

        <h2 class="card-title">Account Details</h2>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Title</label>
                <select name="title" class="select">
                    <?php
                    $titles = ['', 'Mr', 'Mrs', 'Miss', 'Ms', 'Dr', 'Prof', 'Sir', 'Dame'];
                    foreach ($titles as $title):
                    ?>
                        <option value="<?= h($title) ?>" <?= (($user['title'] ?? '') === $title) ? 'selected' : '' ?>>
                            <?= $title === '' ? 'Select title' : h($title) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Position</label>
                <input class="input" type="text" name="position" value="<?= profile_value($user, 'position') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">First Name</label>
                <input class="input" type="text" name="first_name" value="<?= profile_value($user, 'first_name') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Surname</label>
                <input class="input" type="text" name="surname" value="<?= profile_value($user, 'surname') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input class="input" type="email" name="email" value="<?= profile_value($user, 'email') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Phone</label>
                <input class="input" type="text" name="phone" value="<?= profile_value($user, 'phone') ?>">
            </div>

            <div class="form-group span-2">
                <label class="form-label">Company Name</label>
                <input class="input" type="text" name="company_name" value="<?= profile_value($user, 'company_name') ?>">
            </div>
        </div>

        <div class="profile-section-divider"></div>

        <h2 class="card-title">Companies House ID Verification</h2>

        <div class="form-grid">
            <div class="form-group span-2">
                <label class="profile-check-row">
                    <input type="checkbox" name="companies_house_id_verified" value="1" <?= !empty($user['companies_house_id_verified']) ? 'checked' : '' ?>>
                    <span>Companies House ID verification completed</span>
                </label>
            </div>

            <div class="form-group span-2">
                <label class="form-label">Optional ID Verification Code</label>
                <input class="input" type="text" name="companies_house_id_code" value="<?= profile_value($user, 'companies_house_id_code') ?>">
            </div>
        </div>

        <div class="profile-section-divider"></div>

        <h2 class="card-title">Password</h2>

        <div class="form-grid">
            <div class="form-group span-2">
                <label class="form-label">New Password</label>
                <input class="input" type="password" name="new_password" autocomplete="new-password" placeholder="Leave blank to keep current password">
            </div>
        </div>

        <div class="profile-section-divider"></div>

        <div class="form-group">
            <label class="form-label">Profile Notes</label>
            <textarea class="input profile-textarea" name="profile_notes"><?= profile_value($user, 'profile_notes') ?></textarea>
        </div>

        <div class="profile-actions">
            <button type="submit" class="btn btn-primary">Save Profile</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>

    </form>

</div>

<?php require __DIR__ . '/theme/layout/footer.php'; ?>