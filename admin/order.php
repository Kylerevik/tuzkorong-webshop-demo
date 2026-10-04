<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$orderId = input_int($_GET, 'id');

$stmt = db()->prepare('SELECT * FROM orders WHERE id = :id');
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('A rendelés nem található.', 'warning');
    redirect('orders.php');
}

if (is_post()) {
    csrf_verify();
    $newStatus = input_string($_POST, 'status');

    if (isset(ORDER_STATUSES[$newStatus])) {
        $update = db()->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $update->execute(['status' => $newStatus, 'id' => $orderId]);
        set_flash('Az állapot módosítva: ' . ORDER_STATUSES[$newStatus] . '.');
    } else {
        set_flash('Érvénytelen állapot.', 'danger');
    }
    redirect('order.php?id=' . $orderId);
}

$stmt = db()->prepare('SELECT product_name, variant_label, unit_price, quantity FROM order_items WHERE order_id = :id ORDER BY id');
$stmt->execute(['id' => $orderId]);
$items = $stmt->fetchAll();

$adminTitle = 'Rendelés ' . order_number($orderId);
$adminActive = 'orders';
require __DIR__ . '/../includes/admin-header.php';
?>
<p><a href="orders.php">&larr; Vissza a rendelésekhez</a></p>
<h1 class="h3 mb-3">Rendelés <?= e(order_number($orderId)) ?>
    <span class="status-badge status-badge--<?= e($order['status']) ?>"><?= e(ORDER_STATUSES[$order['status']]) ?></span>
</h1>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Termék</th><th class="text-end">Egységár</th><th class="text-end">Db</th><th class="text-end">Összesen</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['product_name']) ?><?= $item['variant_label'] !== '' ? '<br><small class="text-muted">' . e($item['variant_label']) . '</small>' : '' ?></td>
                        <td class="text-end"><?= format_price((int) $item['unit_price']) ?></td>
                        <td class="text-end"><?= (int) $item['quantity'] ?></td>
                        <td class="text-end"><?= format_price((int) $item['unit_price'] * (int) $item['quantity']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="3" class="text-end">Részösszeg</td><td class="text-end"><?= format_price((int) $order['subtotal']) ?></td></tr>
                    <tr><td colspan="3" class="text-end"><?= e($order['shipping_method']) ?></td><td class="text-end"><?= format_price((int) $order['shipping_cost']) ?></td></tr>
                    <tr class="fw-bold"><td colspan="3" class="text-end">Végösszeg</td><td class="text-end"><?= format_price((int) $order['total']) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="panel mb-3">
            <h2 class="h6">Vevő</h2>
            <p class="mb-1"><?= e($order['customer_name']) ?></p>
            <p class="mb-1"><a href="tel:<?= e(preg_replace('/[^+0-9]/', '', $order['phone'])) ?>"><?= e($order['phone']) ?></a></p>
            <p class="mb-0">
                <?= $order['street'] !== null ? e($order['zip'] . ' ' . $order['city']) . '<br>' . e($order['street']) : 'Személyes átvétel' ?>
            </p>
            <?php if ($order['note'] !== null): ?>
            <hr>
            <h2 class="h6">Megjegyzés</h2>
            <p class="mb-0"><?= nl2br(e($order['note'])) ?></p>
            <?php endif; ?>
            <hr>
            <p class="mb-0 text-muted">Leadva: <?= e(date('Y. m. d. H:i', strtotime($order['created_at']))) ?></p>
        </div>

        <form class="panel" method="post" action="order.php?id=<?= $orderId ?>">
            <?= csrf_field() ?>
            <label class="form-label" for="status">Állapot módosítása</label>
            <div class="input-group">
                <select class="form-select" id="status" name="status">
                    <?php foreach (ORDER_STATUSES as $key => $label): ?>
                    <option value="<?= e($key) ?>"<?= $key === $order['status'] ? ' selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary" type="submit">Mentés</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
