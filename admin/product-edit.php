<?php
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/product-admin.php';
require_admin();

function variant_row(array $variant, string|int $index): string
{
    $name = fn (string $field): string => 'variants[' . $index . '][' . $field . ']';
    $hex = preg_match(HEX_COLOR_PATTERN, (string) ($variant['color_hex'] ?? '')) ? $variant['color_hex'] : '#d8c1a0';

    return '<tr data-variant-row>'
        . '<td><input type="hidden" name="' . $name('id') . '" value="' . (int) ($variant['id'] ?? 0) . '">'
        . '<input class="form-control form-control-sm" type="text" name="' . $name('color') . '" value="' . e((string) ($variant['color'] ?? '')) . '" maxlength="40" aria-label="Szín"></td>'
        . '<td><input class="form-control form-control-sm form-control-color" type="color" name="' . $name('color_hex') . '" value="' . e($hex) . '" aria-label="Színkód"></td>'
        . '<td><input class="form-control form-control-sm" type="text" name="' . $name('size') . '" value="' . e((string) ($variant['size'] ?? '')) . '" maxlength="40" aria-label="Méret"></td>'
        . '<td><input class="form-control form-control-sm" type="number" name="' . $name('price_diff') . '" value="' . e((string) ($variant['price_diff'] ?? '0')) . '" step="1" aria-label="Árkülönbség"></td>'
        . '<td><input class="form-control form-control-sm" type="number" name="' . $name('stock') . '" value="' . e((string) ($variant['stock'] ?? '0')) . '" min="0" step="1" aria-label="Készlet"></td>'
        . '<td class="text-end"><button class="btn btn-sm btn-outline-danger" type="button" data-remove-variant>Törlés</button></td>'
        . '</tr>';
}

$id = (int) ($_GET['id'] ?? 0);
$existing = $id > 0 ? admin_fetch_product($id) : null;

if ($id > 0 && $existing === null) {
    set_flash('A termék nem található.', 'warning');
    redirect('products.php');
}

$categories = db()->query('SELECT id, name FROM categories ORDER BY id')->fetchAll();
$images = admin_available_images();
$errors = [];

if ($existing !== null) {
    $values = $existing;
    $values['variants'] = admin_fetch_variants($id);
} else {
    $values = [
        'name' => '', 'category_id' => 0, 'base_price' => '', 'description' => '', 'image' => '',
        'is_featured' => 0, 'is_active' => 1, 'variants' => [['id' => 0, 'stock' => 0, 'price_diff' => 0]],
    ];
}

if (is_post()) {
    csrf_verify();
    [$values, $errors] = validate_product_input($_POST, array_map('intval', array_column($categories, 'id')), $images);

    if ($errors === []) {
        try {
            save_product(db(), $values, $id);
            set_flash($id > 0 ? 'A termék módosításai mentve.' : 'Az új termék felvéve.');
            redirect('products.php');
        } catch (PDOException $e) {
            error_log('Termék mentési hiba: ' . $e->getMessage());
            $errors['form'] = 'A mentés most nem sikerült. Kérjük, próbálja meg újra.';
        }
    }

    // Hibás beküldésnél a változatok sorai úgy maradnak, ahogy az admin kitöltötte őket
    $values['variants'] = is_array($_POST['variants'] ?? null) ? $_POST['variants'] : [];
}

$adminTitle = $id > 0 ? 'Termék szerkesztése' : 'Új termék';
$adminActive = 'products';
require __DIR__ . '/../includes/admin-header.php';
?>
<p><a href="products.php">&larr; Vissza a termékekhez</a></p>
<h1 class="h3 mb-3"><?= e($adminTitle) ?></h1>

<?php if (isset($errors['form'])): ?>
<div class="alert alert-danger" role="alert"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form method="post" action="product-edit.php<?= $id > 0 ? '?id=' . $id : '' ?>">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel">
                <div class="mb-3">
                    <label class="form-label" for="name">Név</label>
                    <input class="<?= field_class($errors, 'name') ?>" type="text" id="name" name="name" value="<?= e($values['name']) ?>" maxlength="120" required>
                    <?= field_error($errors, 'name') ?>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="category_id">Kategória</label>
                        <select class="form-select<?= isset($errors['category_id']) ? ' is-invalid' : '' ?>" id="category_id" name="category_id" required>
                            <option value="">Válasszon...</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>"<?= (int) $values['category_id'] === (int) $category['id'] ? ' selected' : '' ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= field_error($errors, 'category_id') ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="base_price">Alapár (Ft)</label>
                        <input class="<?= field_class($errors, 'base_price') ?>" type="number" id="base_price" name="base_price" value="<?= e((string) $values['base_price']) ?>" min="0" step="1" required>
                        <?= field_error($errors, 'base_price') ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Leírás</label>
                    <textarea class="<?= field_class($errors, 'description') ?>" id="description" name="description" rows="5" maxlength="2000" required><?= e($values['description']) ?></textarea>
                    <?= field_error($errors, 'description') ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="image">Kép</label>
                    <select class="form-select<?= isset($errors['image']) ? ' is-invalid' : '' ?>" id="image" name="image">
                        <option value="">Nincs kép (helyettesítő ábra)</option>
                        <?php foreach ($images as $image): ?>
                        <option value="<?= e($image) ?>"<?= $values['image'] === $image ? ' selected' : '' ?>><?= e($image) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Új képet az img/products mappába másolva lehet felvenni, utána megjelenik a listában.</div>
                    <?= field_error($errors, 'image') ?>
                </div>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"<?= (int) $values['is_active'] === 1 ? ' checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Látható a webshopban</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1"<?= (int) $values['is_featured'] === 1 ? ' checked' : '' ?>>
                    <label class="form-check-label" for="is_featured">Kiemelt a főoldalon</label>
                </div>
            </div>
        </div>
    </div>

    <div class="panel mt-4">
        <h2 class="h5">Változatok</h2>
        <p class="text-muted">A szín és a méret mezőt vagy minden sorban ki kell tölteni, vagy egyikben sem. Az árkülönbség az alapárhoz adódik hozzá (lehet negatív is).</p>
        <?php if (isset($errors['variants'])): ?>
        <div class="alert alert-danger" role="alert"><?= e($errors['variants']) ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table align-middle variant-table">
                <thead><tr><th>Szín</th><th>Színkód</th><th>Méret</th><th>Árkülönbség (Ft)</th><th>Készlet (db)</th><th></th></tr></thead>
                <tbody data-variant-rows>
                    <?php foreach ($values['variants'] as $index => $variant): ?>
                    <?= variant_row($variant, $index) ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button class="btn btn-outline-secondary btn-sm" type="button" data-add-variant>Új változat</button>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary" type="submit">Mentés</button>
        <a class="btn btn-outline-secondary" href="products.php">Mégse</a>
    </div>
</form>

<template id="variant-row-template"><?= variant_row(['id' => 0, 'stock' => 0, 'price_diff' => 0], '__INDEX__') ?></template>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
