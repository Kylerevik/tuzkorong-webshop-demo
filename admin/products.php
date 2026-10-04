<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

if (is_post()) {
    csrf_verify();
    $deleteId = input_int($_POST, 'delete_id');

    $stmt = db()->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id' => $deleteId]);

    if ($stmt->rowCount() > 0) {
        set_flash('A terméket töröltük. A korábbi rendeléseken továbbra is látszik.');
    } else {
        set_flash('A termék nem található.', 'warning');
    }
    redirect('products.php');
}

$products = db()->query(
    'SELECT p.id, p.name, p.image, p.base_price, p.is_featured, p.is_active, c.name AS category_name,
            COUNT(v.id) AS variant_count, COALESCE(SUM(v.stock), 0) AS total_stock
     FROM products p
     JOIN categories c ON c.id = p.category_id
     LEFT JOIN product_variants v ON v.product_id = p.id
     GROUP BY p.id, p.name, p.image, p.base_price, p.is_featured, p.is_active, c.name
     ORDER BY c.id, p.name'
)->fetchAll();

$adminTitle = 'Termékek';
$adminActive = 'products';
require __DIR__ . '/../includes/admin-header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Termékek</h1>
    <a class="btn btn-primary" href="product-edit.php">Új termék</a>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr><th></th><th>Név</th><th>Kategória</th><th class="text-end">Alapár</th><th class="text-end">Változat</th><th class="text-end">Készlet</th><th>Állapot</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><img class="admin-thumb" src="../<?= e(product_image_url($product['image'])) ?>" width="48" height="48" alt=""></td>
                <td><strong><?= e($product['name']) ?></strong></td>
                <td><?= e($product['category_name']) ?></td>
                <td class="text-end"><?= format_price((int) $product['base_price']) ?></td>
                <td class="text-end"><?= (int) $product['variant_count'] ?></td>
                <td class="text-end"><?= (int) $product['total_stock'] ?> db</td>
                <td>
                    <?php if ((int) $product['is_active'] === 0): ?><span class="badge text-bg-secondary">Rejtett</span><?php endif; ?>
                    <?php if ((int) $product['is_featured'] === 1): ?><span class="badge text-bg-warning">Kiemelt</span><?php endif; ?>
                </td>
                <td class="text-end text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" href="product-edit.php?id=<?= (int) $product['id'] ?>">Szerkesztés</a>
                    <form class="d-inline" method="post" action="products.php" data-confirm="Biztosan törli ezt a terméket: <?= e($product['name']) ?>?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="delete_id" value="<?= (int) $product['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Törlés</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
