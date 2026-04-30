<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (is_logged_in()) {
    redirect_to('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if (attempt_login($email, $password)) {
        redirect_to('index.php');
    } else {
        $error = 'Invalid email or password.';
    }
}

$flash = get_flash();
require __DIR__ . '/views/login_view.php';