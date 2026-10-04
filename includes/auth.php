<?php
declare(strict_types=1);

function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('login.php');
    }
}

function attempt_admin_login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, password_hash FROM admins WHERE username = :username');
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        return true;
    }

    // Lassítás a találgatásos próbálkozások ellen
    usleep(500000);
    return false;
}

function admin_logout(): void
{
    $_SESSION = [];
    session_destroy();
}
