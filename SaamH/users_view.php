<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>User Management - <?= h((string) cfg('app_name')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= h((string) cfg('base_url')) ?>/style.css">
</head>
<body>
<div class="wrap wrap-dashboard">
    <?php require __DIR__ . '/nav.php'; ?>

    <?php if ($flash): ?>
        <div class="<?= $flash['type'] === 'error' ? 'flash-error' : 'flash-ok' ?>">
            <?= h((string) $flash['message']) ?>
        </div>
    <?php endif; ?>

    <h1>User Management</h1>

    <?php if ($userToEdit): ?>
        <h2>Edit User: <?= h($userToEdit['email']) ?></h2>
        <form method="post" action="index.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= $userToEdit['id'] ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= h($userToEdit['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">New Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="user" <?= $userToEdit['role'] === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $userToEdit['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn">Update User</button>
            <a href="index.php">Cancel</a>
        </form>
    <?php else: ?>
        <div class="grid">
            <div>
                <h2>All Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= h($user['email']) ?></td>
                            <td><?= h($user['role']) ?></td>
                            <td><?= h(date('Y-m-d', strtotime($user['created_at']))) ?></td>
                            <td>
                                <a href="?action=impersonate&id=<?= $user['id'] ?>">View Dashboard</a>
                                | <a href="?action=edit&id=<?= $user['id'] ?>">Edit</a>
                                <?php if ($user['id'] !== get_current_user_id()): ?>
                                | <a href="?action=delete&id=<?= $user['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div>
                <h2>Create New User</h2>
                <form method="post" action="index.php">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Create User</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>