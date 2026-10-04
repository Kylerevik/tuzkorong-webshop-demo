<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/orders.php';

$shippingMethods = [];
foreach (fetch_shipping_methods() as $method) {
    $shippingMethods[(int) $method['id']] = $method;
}

$values = [
    'name' => '', 'phone' => '', 'zip' => '', 'city' => '', 'street' => '', 'note' => '',
    'shipping_method_id' => (int) array_key_first($shippingMethods),
];
$errors = [];

if (is_post()) {
    csrf_verify();

    // Csak robotok töltik ki a vevő elől rejtett mezőt
    if (($_POST['website'] ?? '') !== '') {
        http_response_code(400);
        exit('Érvénytelen kérés.');
    }

    [$values, $errors] = validate_checkout($_POST, $shippingMethods);

    $cart = parse_cart_input(decode_cart_json(input_string($_POST, 'cart')) ?? []);
    if ($cart === []) {
        $errors['cart'] = 'A kosara üres, ezért nem tudjuk rögzíteni a rendelést.';
    }

    if ($errors === []) {
        try {
            $orderId = place_order(db(), $values, $cart, $shippingMethods[$values['shipping_method_id']]);
            $_SESSION['last_order_id'] = $orderId;
            redirect('confirmation.php');
        } catch (OrderException $e) {
            $errors['cart'] = $e->getMessage();
        } catch (PDOException $e) {
            error_log('Rendelés rögzítési hiba: ' . $e->getMessage());
            $errors['cart'] = 'A rendelést most nem sikerült rögzíteni. Kérjük, próbálja meg később.';
        }
    }
}

$selectedMethod = $shippingMethods[$values['shipping_method_id']] ?? reset($shippingMethods);
$addressRequired = (int) $selectedMethod['requires_address'] === 1;

$pageTitle = 'Pénztár';
$pageDescription = 'Rendelés leadása a Tűzkorong webshopban: szállítási adatok és szállítási mód megadása.';
$pageScripts = ['js/checkout.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <h1 class="section__title">Pénztár</h1>

        <p class="text-muted" data-checkout-loading>Kosár betöltése...</p>
        <div class="alert alert-warning" role="status" data-checkout-notice hidden></div>

        <div data-checkout-empty hidden>
            <p>A kosara üres, ezért nincs mit megrendelni.</p>
            <a class="btn btn-primary" href="products.php">Termékek böngészése</a>
        </div>

        <?php if (isset($errors['cart'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= e($errors['cart']) ?> <a class="alert-link" href="cart.php">Vissza a kosárhoz</a>
        </div>
        <?php endif; ?>

        <form class="row g-4" method="post" action="checkout.php" data-checkout-form hidden>
            <?= csrf_field() ?>
            <input type="hidden" name="cart" value="" data-cart-field>
            <div class="trap" aria-hidden="true">
                <label for="website">Ezt a mezőt hagyja üresen</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="col-lg-7">
                <fieldset class="checkout-section">
                    <legend class="checkout-section__title">Elérhetőség</legend>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="name">Teljes név</label>
                            <input class="<?= field_class($errors, 'name') ?>" type="text" id="name" name="name" value="<?= e($values['name']) ?>"
                                   required minlength="3" maxlength="100" autocomplete="name">
                            <?= field_error($errors, 'name') ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="phone">Telefonszám</label>
                            <input class="<?= field_class($errors, 'phone') ?>" type="tel" id="phone" name="phone" value="<?= e($values['phone']) ?>"
                                   required pattern="\+?[0-9 \(\)\/\-]{7,20}" maxlength="20" autocomplete="tel" placeholder="+36 30 555 0123">
                            <?= field_error($errors, 'phone') ?>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="checkout-section">
                    <legend class="checkout-section__title">Szállítási mód</legend>
                    <?php foreach ($shippingMethods as $method): ?>
                    <div class="shipping-option">
                        <input class="form-check-input" type="radio" name="shipping_method_id" id="shipping-<?= (int) $method['id'] ?>"
                               value="<?= (int) $method['id'] ?>"
                               data-price="<?= (int) $method['price'] ?>"
                               data-free-from="<?= $method['free_from'] !== null ? (int) $method['free_from'] : '' ?>"
                               data-requires-address="<?= (int) $method['requires_address'] ?>"
                               <?= (int) $method['id'] === $values['shipping_method_id'] ? 'checked' : '' ?>>
                        <label class="shipping-option__label" for="shipping-<?= (int) $method['id'] ?>">
                            <span class="shipping-option__name"><?= e($method['name']) ?></span>
                            <span class="shipping-option__description"><?= e($method['description']) ?></span>
                        </label>
                        <span class="shipping-option__price"><?= (int) $method['price'] === 0 ? 'Ingyenes' : format_price((int) $method['price']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?= field_error($errors, 'shipping_method_id') ?>
                </fieldset>

                <fieldset class="checkout-section" data-address-fields<?= $addressRequired ? '' : ' hidden' ?>>
                    <legend class="checkout-section__title">Szállítási cím</legend>
                    <div class="row g-3">
                        <div class="col-12 col-sm-4 col-md-3">
                            <label class="form-label" for="zip">Irányítószám</label>
                            <input class="<?= field_class($errors, 'zip') ?>" type="text" id="zip" name="zip" value="<?= e($values['zip']) ?>"
                                   pattern="[0-9]{4}" maxlength="4" inputmode="numeric" autocomplete="postal-code"
                                   <?= $addressRequired ? 'required' : '' ?> data-address-input>
                            <?= field_error($errors, 'zip') ?>
                        </div>
                        <div class="col-12 col-sm-8 col-md-9">
                            <label class="form-label" for="city">Település</label>
                            <input class="<?= field_class($errors, 'city') ?>" type="text" id="city" name="city" value="<?= e($values['city']) ?>"
                                   maxlength="60" autocomplete="address-level2" <?= $addressRequired ? 'required' : '' ?> data-address-input>
                            <?= field_error($errors, 'city') ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="street">Utca, házszám</label>
                            <input class="<?= field_class($errors, 'street') ?>" type="text" id="street" name="street" value="<?= e($values['street']) ?>"
                                   minlength="3" maxlength="120" autocomplete="street-address" <?= $addressRequired ? 'required' : '' ?> data-address-input>
                            <?= field_error($errors, 'street') ?>
                        </div>
                    </div>
                </fieldset>

                <div class="checkout-section">
                    <label class="form-label" for="note">Megjegyzés a rendeléshez <span class="text-muted">(nem kötelező)</span></label>
                    <textarea class="<?= field_class($errors, 'note') ?>" id="note" name="note" rows="3" maxlength="500"><?= e($values['note']) ?></textarea>
                    <?= field_error($errors, 'note') ?>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="summary-card">
                    <h2 class="summary-card__title">Rendelés összegzése</h2>
                    <ul class="summary-list" data-summary-list></ul>
                    <p class="summary-row"><span>Részösszeg</span><span data-summary-subtotal></span></p>
                    <p class="summary-row"><span>Szállítás</span><span data-summary-shipping></span></p>
                    <p class="summary-row summary-row--total"><span>Végösszeg</span><strong data-summary-total></strong></p>
                    <p class="summary-hint">Online fizetés nincs: a rendelést rögzítjük, a fizetés az átvételkor történik.</p>
                    <button class="btn btn-primary btn-lg w-100" type="submit">Megrendelem</button>
                </div>
            </div>
        </form>
    </div>
</section>

<template id="summary-line-template">
    <li class="summary-line">
        <img width="56" height="56" alt="" data-line-image>
        <div class="summary-line__info">
            <span class="summary-line__name" data-line-name></span>
            <span class="summary-line__label" data-line-label></span>
        </div>
        <span class="summary-line__total" data-line-total></span>
    </li>
</template>
<?php require __DIR__ . '/includes/footer.php'; ?>
