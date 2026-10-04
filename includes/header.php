<?php
$siteName = 'Tűzkorong Kerámiaműhely';
$pageTitle = $pageTitle ?? '';
$fullTitle = $pageTitle !== '' ? $pageTitle . ' | ' . $siteName : $siteName . ' | Kézzel készített kerámia Pécsről';
$pageDescription = $pageDescription ?? 'Kézzel korongozott bögrék, tányérok és vázák a pécsi Tűzkorong műhelyből. Rendeljen online, vagy vegye át személyesen.';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($fullTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:locale" content="hu_HU">
    <meta name="theme-color" content="#b5542f">
    <link rel="icon" href="img/logo.svg" type="image/svg+xml">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
<?= $extraHead ?? '' ?>
</head>
<body>
<a class="skip-link" href="#main">Ugrás a tartalomra</a>
<header class="site-header">
    <nav class="navbar navbar-expand-md" aria-label="Főmenü">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="img/logo.svg" width="36" height="36" alt="">
                <span>Tűzkorong</span>
            </a>
            <a class="cart-link order-md-last ms-auto ms-md-3" href="cart.php" aria-label="Kosár">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7V6a3 3 0 0 1 6 0v1"/></svg>
                <span class="cart-link__text">Kosár</span>
                <span class="cart-badge" data-cart-count hidden>0</span>
            </a>
            <button class="navbar-toggler ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav" aria-controls="main-nav" aria-expanded="false" aria-label="Menü megnyitása">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="main-nav">
                <ul class="navbar-nav ms-md-4">
                    <li class="nav-item"><a class="nav-link<?= $activePage === 'home' ? ' active' : '' ?>" href="index.php">Főoldal</a></li>
                    <li class="nav-item"><a class="nav-link<?= $activePage === 'products' ? ' active' : '' ?>" href="products.php">Termékek</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#muhely">A műhelyről</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#szallitas">Szállítás</a></li>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main id="main">
