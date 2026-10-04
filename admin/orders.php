<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$status = $_GET['status'] ?? '';
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
            <tr><th>Szám</th><th>Időpont</th><th>Vevő</th><th>Szállítás</th><th class="text-end">Összeg</th><th>Állapot</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><strong><?= e(order_number((int) $order['id'])) ?></strong></td>
                <td><?= e(date('Y. m. d. H:i', strtotime($order['created_at']))) ?></td>
                <td><?= e($order['customer_name']) ?><br><small class="text-muted"><?= e($order['phone']) ?></small></td>
                <td><?= e($order['shipping_method']) ?></td>
                <td class="text-end"><?= format_price((int) $order['total']) ?></td>
                <td><span class="status-badge status-badge--<?= e($order['status']) ?>"><?= e(ORDER_STATUSES[$order['status']]) ?></span></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="order.php?id=<?= (int) $order['id'] ?>">Részletek</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
