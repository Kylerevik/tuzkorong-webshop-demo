<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$status = input_string($_GET, 'status');
if (!isset(ORDER_STATUSES[$status])) {
    $status = '';
}

if ($status !== '') {
    $stmt = db()->prepare('SELECT id, customer_name, phone, shipping_method, total, status, created_at FROM orders WHERE status = :status ORDER BY created_at DESC, id DESC');
    $stmt->execute(['status' => $status]);
} else {
    $stmt = db()->query('SELECT id, customer_name, phone, shipping_method, total, status, created_at FROM orders ORDER BY created_at DESC, id DESC');
}
$orders = $stmt->fetchAll();

$adminTitle = 'Rendelések';
$adminActive = 'orders';
require __DIR__ . '/../includes/admin-header.php';
?>
<h1 class="h3 mb-3">Rendelések</h1>

<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link<?= $status === '' ? ' active' : '' ?>" href="orders.php">Összes</a></li>
    <?php foreach (ORDER_STATUSES as $key => $label): ?>
    <li class="nav-item"><a class="nav-link<?= $status === $key ? ' active' : '' ?>" href="orders.php?status=<?= e($key) ?>"><?= e($label) ?></a></li>
    <?php endforeach; ?>
</ul>

<?php if ($orders === []): ?>
<p class="text-muted">Ebben az állapotban nincs rendelés.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th>Szám</th><th class="d-none d-md-table-cell">Időpont</th><th>Vevő</th><th class="d-none d-lg-table-cell">Szállítás</th><th class="col-compact text-end">Összeg</th><th>Állapot</th><th class="d-none d-md-table-cell"></th></tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td class="text-nowrap"><a href="order.php?id=<?= (int) $order['id'] ?>"><strong><?= e(order_number((int) $order['id'])) ?></strong></a></td>
                <td class="d-none d-md-table-cell"><?= e(date('Y. m. d. H:i', strtotime($order['created_at']))) ?></td>
                <td><?= e($order['customer_name']) ?><small class="d-none d-md-block text-muted"><?= e($order['phone']) ?></small></td>
                <td class="d-none d-lg-table-cell"><?= e($order['shipping_method']) ?></td>
                <td class="col-compact text-end text-nowrap"><?= format_price((int) $order['total']) ?></td>
                <td><span class="status-badge status-badge--<?= e($order['status']) ?>"><?= e(ORDER_STATUSES[$order['status']]) ?></span></td>
                <td class="d-none d-md-table-cell text-end"><a class="btn btn-sm btn-outline-secondary" href="order.php?id=<?= (int) $order['id'] ?>">Részletek</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
