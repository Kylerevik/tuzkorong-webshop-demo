<?php
require __DIR__ . '/includes/bootstrap.php';

$freeShippingFrom = null;
foreach (fetch_shipping_methods() as $method) {
    if ($method['free_from'] !== null) {
        $freeShippingFrom = (int) $method['free_from'];
        break;
    }
}

$pageTitle = 'Kosár';
$pageDescription = 'A kosarában lévő termékek a Tűzkorong webshopban.';
$pageScripts = ['js/cart-page.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <h1 class="section__title">Kosár</h1>

        <p class="text-muted" data-cart-loading>Kosár betöltése...</p>
        <div class="alert alert-warning" role="status" data-cart-notice hidden></div>

        <div data-cart-empty hidden>
            <p>A kosara jelenleg üres.</p>
            <a class="btn btn-primary" href="products.php">Termékek böngészése</a>
        </div>

        <div class="row g-4" data-cart-content<?= $freeShippingFrom !== null ? ' data-free-shipping-from="' . $freeShippingFrom . '"' : '' ?> hidden>
            <div class="col-lg-8">
                <ul class="cart-list" data-cart-list></ul>
            </div>
            <div class="col-lg-4">
                <div class="summary-card">
                    <h2 class="summary-card__title">Összegzés</h2>
                    <p class="summary-row"><span>Részösszeg</span><strong data-cart-subtotal></strong></p>
                    <p class="summary-hint" data-cart-hint></p>
                    <a class="btn btn-primary btn-lg w-100" href="checkout.php">Tovább a pénztárhoz</a>
                </div>
            </div>
        </div>
    </div>
</section>

<template id="cart-line-template">
    <li class="cart-line">
        <a class="cart-line__image" data-line-image-link><img width="96" height="96" alt="" data-line-image></a>
        <div class="cart-line__info">
            <a class="cart-line__name" data-line-name></a>
            <p class="cart-line__label" data-line-label></p>
            <p class="cart-line__unit" data-line-unit></p>
        </div>
        <div class="quantity">
            <button class="quantity__button" type="button" data-line-step="-1" aria-label="Kevesebb">&minus;</button>
            <input class="quantity__input" type="number" min="1" inputmode="numeric" aria-label="Mennyiség" data-line-quantity>
            <button class="quantity__button" type="button" data-line-step="1" aria-label="Több">+</button>
        </div>
        <p class="cart-line__total" data-line-total></p>
        <button class="cart-line__remove" type="button" data-line-remove>Törlés</button>
    </li>
</template>
<?php require __DIR__ . '/includes/footer.php'; ?>
