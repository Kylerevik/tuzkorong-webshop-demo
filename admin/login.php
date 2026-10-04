<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';

if (is_admin()) {
    redirect('index.php');
}

$error = '';

if (is_post()) {
    csrf_verify();

    if (attempt_admin_login(trim(input_string($_POST, 'username')), input_string($_POST, 'password'))) {
        redirect('index.php');
    }
    $error = 'Hibás felhasználónév vagy jelszó.';
}

$adminTitle = 'Belépés';
$showAdminNav = false;
require __DIR__ . '/../includes/admin-header.php';
?>
<div class="login-box">
    <h1 class="h3 mb-1">Tűzkorong admin</h1>
    <p class="text-muted">Termékek és rendelések kezelése.</p>

    <div class="demo-credentials">
        <strong>Demó belépés</strong><br>
        Felhasználónév: <code>admin</code><br>
        Jelszó: <code>Agyag2026</code>
    </div>

    <?php if ($error !== ''): ?>
    <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="username">Felhasználónév</label>
            <input class="form-control" type="text" id="username" name="username" required autocomplete="username" autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label" for="password">Jelszó</label>
            <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button class="btn btn-primary w-100" type="submit">Belépés</button>
    </form>
    <p class="mt-3 mb-0"><a href="../index.php">Vissza a webshopba</a></p>
</div>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
