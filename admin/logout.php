<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';

if (is_post()) {
    csrf_verify();
    admin_logout();
}

redirect('login.php');
