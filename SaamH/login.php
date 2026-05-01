<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (is_logged_in() && is_admin()) {
    redirect_to('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (attempt_login($email, $password)) {
        if (is_admin()) {
            redirect_to('index.php');
        } else {
            logout();
            $error = 'You do not have admin privileges to access this panel.';
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

$flash = get_flash();
require __DIR__ . '/login_view.php';