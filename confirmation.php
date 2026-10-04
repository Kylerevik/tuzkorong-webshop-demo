<?php
require __DIR__ . '/includes/bootstrap.php';

$orderId = (int) ($_SESSION['last_order_id'] ?? 0);
if ($orderId === 0) {
    redirect('index.php');
}

$stmt = db()->prepare('SELECT * FROM orders WHERE id = :id');
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch();
if (!$order) {
    redirect('index.php');
}

$stmt = db()->prepare('SELECT product_name, variant_label, unit_price, quantity FROM order_items WHERE order_id = :id ORDER BY id');
$stmt->execute(['id' => $orderId]);
$items = $stmt->fetchAll();

$pageTitle = 'Rendelés visszaigazolása';
$pageDescription = 'A rendelését rögzítettük.';
require __DIR__ . '/includes/header.php';
?>
<section class="section" data-clear-cart>
    <div class="container">
        <div class="confirmation">
            <p class="eyebrow">Rendelés rögzítve</p>
            <h1 class="section__title">Köszönjük a rendelését, <?= e($order['customer_name']) ?>!</h1>
            <p>A rendelés száma: <strong><?= e(order_number((int) $order['id'])) ?></strong>. A műhely hamarosan felhívja a megadott telefonszámon, hogy egyeztessen az átvételről vagy a szállításról. E-mailt nem küldünk.</p>

            <div class="summary-card">
                <h2 class="summary-card__title">Rendelt termékek</h2>
                <ul class="summary-list">
                    <?php foreach ($items as $item): ?>
                    <li class="summary-line">
                        <div class="summary-line__info">
                            <span class="summary-line__name"><?= e($item['product_name']) ?> &times; <?= (int) $item['quantity'] ?></span>
                            <?php if ($item['variant_label'] !== ''): ?><span class="summary-line__label"><?= e($item['variant_label']) ?></span><?php endif; ?>
                        </div>
                        <span class="summary-line__total"><?= format_price((int) $item['unit_price'] * (int) $item['quantity']) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <p class="summary-row"><span>Részösszeg</span><span><?= format_price((int) $order['subtotal']) ?></span></p>
                <p class="summary-row"><span><?= e($order['shipping_method']) ?></span><span><?= (int) $order['shipping_cost'] === 0 ? 'Ingyenes' : format_price((int) $order['shipping_cost']) ?></span></p>
                <p class="summary-row summary-row--total"><span>Végösszeg</span><strong><?= format_price((int) $order['total']) ?></strong></p>
            </div>

            <dl class="confirmation__details">
                <dt>Név</dt><dd><?= e($order['customer_name']) ?></dd>
                <dt>Telefonszám</dt><dd><?= e($order['phone']) ?></dd>
                <dt>Szállítási cím</dt>
                <dd><?= $order['street'] !== null ? e($order['zip'] . ' ' . $order['city'] . ', ' . $order['street']) : 'Személyes átvétel a műhelyben (Pécs, Tímár utca 11.)' ?></dd>
                <?php if ($order['note'] !== null): ?><dt>Megjegyzés</dt><dd><?= nl2br(e($order['note'])) ?></dd><?php endif; ?>
            </dl>

            <a class="btn btn-primary" href="products.php">Vissza a termékekhez</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
