<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

logout();
redirect_to('login.php');