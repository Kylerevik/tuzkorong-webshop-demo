<?php
$adminTitle = $adminTitle ?? 'Admin';
$adminActive = $adminActive ?? '';
$showAdminNav = $showAdminNav ?? true;
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle) ?> | Tűzkorong admin</title>
    <link rel="icon" href="../img/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php if ($showAdminNav): ?>
<nav class="navbar navbar-expand-md admin-nav" aria-label="Admin menü">
    <div class="container-xl">
        <a class="navbar-brand" href="index.php">Tűzkorong admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#admin-menu" aria-controls="admin-menu" aria-expanded="false" aria-label="Menü megnyitása">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="admin-menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link<?= $adminActive === 'dashboard' ? ' active' : '' ?>" href="index.php">Áttekintés</a></li>
                <li class="nav-item"><a class="nav-link<?= $adminActive === 'orders' ? ' active' : '' ?>" href="orders.php">Rendelések</a></li>
                <li class="nav-item"><a class="nav-link<?= $adminActive === 'products' ? ' active' : '' ?>" href="products.php">Termékek</a></li>
                <li class="nav-item"><a class="nav-link" href="../index.php">Webshop megtekintése</a></li>
            </ul>
            <form method="post" action="logout.php">
                <?= csrf_field() ?>
                <button class="btn btn-outline-light btn-sm" type="submit">Kilépés</button>
            </form>
        </div>
    </div>
</nav>
<?php endif; ?>
<main class="container-xl admin-main">
<?php if ($flash !== null): ?>
    <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
<?php endif; ?>
