<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();
require_admin();

$action = $_REQUEST['action'] ?? 'list';
$userId = (int) ($_REQUEST['id'] ?? 0);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'create') {
            create_user(
                (string) ($_POST['email'] ?? ''),
                (string) ($_POST['password'] ?? ''),
                (string) ($_POST['role'] ?? 'user')
            );
            set_flash('User created.');
            redirect_to('index.php');
        }

        if ($action === 'update' && $userId > 0) {
            update_user(
                $userId,
                (string) ($_POST['email'] ?? ''),
                (string) ($_POST['role'] ?? 'user'),
                (string) ($_POST['password'] ?? '') ?: null
            );
            set_flash('User updated.');
            redirect_to('index.php');
        }
    }

    if ($action === 'delete' && $userId > 0) {
        // Prevent admin from deleting themselves
        if ($userId === get_current_user_id()) {
            set_flash('You cannot delete your own account.', 'error');
        } else {
            delete_user($userId);
            set_flash('User deleted.');
        }
        redirect_to('index.php');
    }

    if ($action === 'impersonate' && $userId > 0) {
        if ($userId !== get_current_user_id()) {
            $_SESSION['original_admin_id'] = get_current_user_id();
            $_SESSION['user_id'] = $userId;
        }
        redirect_to((string) cfg('main_url') . '/index.php');
    }
} catch (Throwable $e) {
    set_flash($e->getMessage(), 'error');
    redirect_to('index.php');
}

$users = [];
$userToEdit = null;

if ($action === 'edit' && $userId > 0) {
    $userToEdit = find_user_by_id($userId);
} else {
    $users = get_all_users();
}

$flash = get_flash();
require __DIR__ . '/users_view.php';