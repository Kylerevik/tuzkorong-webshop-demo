<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$counts = db()->query(
    "SELECT SUM(status = 'new') AS new_orders, SUM(status = 'processing') AS processing_orders, COUNT(*) AS all_orders FROM orders"
)->fetch();
$productCount = (int) db()->query('SELECT COUNT(*) FROM products WHERE is_active = 1')->fetchColumn();

$lowStock = db()->query(
    'SELECT p.id, p.name, v.color, v.size, v.stock
     FROM product_variants v JOIN products p ON p.id = v.product_id
     WHERE v.stock <= 2 AND p.is_active = 1
     ORDER BY v.stock, p.name LIMIT 8'
)->fetchAll();

$latestOrders = db()->query('SELECT id, customer_name, total, status, created_at FROM orders ORDER BY created_at DESC, id DESC LIMIT 5')->fetchAll();

$adminTitle = 'Áttekintés';
$adminActive = 'dashboard';
require __DIR__ . '/../includes/admin-header.php';
?>
<h1 class="h3 mb-4">Áttekintés</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card"><span class="stat-card__value"><?= (int) $counts['new_orders'] ?></span><span class="stat-card__label">új rendelés</span></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><span class="stat-card__value"><?= (int) $counts['processing_orders'] ?></span><span class="stat-card__label">feldolgozás alatt</span></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><span class="stat-card__value"><?= (int) $counts['all_orders'] ?></span><span class="stat-card__label">rendelés összesen</span></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><span class="stat-card__value"><?= $productCount ?></span><span class="stat-card__label">aktív termék</span></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <h2 class="h5">Legutóbbi rendelések</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Szám</th><th>Vevő</th><th>Összeg</th><th>Állapot</th></tr></thead>
                <tbody>
                <?php foreach ($latestOrders as $order): ?>
                    <tr>
                        <td><a href="order.php?id=<?= (int) $order['id'] ?>"><?= e(order_number((int) $order['id'])) ?></a></td>
                        <td><?= e($order['customer_name']) ?></td>
                        <td><?= format_price((int) $order['total']) ?></td>
                        <td><span class="status-badge status-badge--<?= e($order['status']) ?>"><?= e(ORDER_STATUSES[$order['status']]) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-5">
        <h2 class="h5">Fogyó készlet</h2>
        <?php if ($lowStock === []): ?>
        <p class="text-muted">Minden változatból van legalább három darab.</p>
        <?php else: ?>
        <ul class="list-group">
            <?php foreach ($lowStock as $row): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <a href="product-edit.php?id=<?= (int) $row['id'] ?>"><?= e($row['name']) ?><?= variant_label($row['color'], $row['size']) !== '' ? ' (' . e(variant_label($row['color'], $row['size'])) . ')' : '' ?></a>
                <span class="badge text-bg-<?= (int) $row['stock'] === 0 ? 'danger' : 'warning' ?>"><?= (int) $row['stock'] ?> db</span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
