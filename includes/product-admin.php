<?php
declare(strict_types=1);

const HEX_COLOR_PATTERN = '/^#[0-9a-fA-F]{6}$/';

function admin_fetch_product(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);

    return $stmt->fetch() ?: null;
}

function admin_fetch_variants(int $productId): array
{
    $stmt = db()->prepare('SELECT id, color, color_hex, size, price_diff, stock FROM product_variants WHERE product_id = :id ORDER BY id');
    $stmt->execute(['id' => $productId]);

    return $stmt->fetchAll();
}

/**
 * A termékképek mappájában lévő fájlok, ezek közül választhat az admin.
 */
function admin_available_images(): array
{
    $allowed = ['svg', 'jpg', 'jpeg', 'png', 'webp'];
    $names = [];

    foreach (scandir(__DIR__ . '/../img/products') ?: [] as $file) {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if ($file !== 'placeholder.svg' && in_array($extension, $allowed, true)) {
            $names[] = $file;
        }
    }

    return $names;
}

/**
 * @return array{0: array, 1: array} a megtisztított adatok és a hibák
 */
function validate_product_input(array $input, array $categoryIds, array $images): array
{
    $clean = [
        'name' => trim(input_string($input, 'name')),
        'category_id' => input_int($input, 'category_id'),
        'base_price' => filter_var($input['base_price'] ?? '', FILTER_VALIDATE_INT),
        'description' => trim(input_string($input, 'description')),
        'image' => input_string($input, 'image'),
        'is_featured' => isset($input['is_featured']) ? 1 : 0,
        'is_active' => isset($input['is_active']) ? 1 : 0,
        'variants' => [],
    ];
    $errors = [];

    if ($clean['name'] === '' || mb_strlen($clean['name']) > 120) {
        $errors['name'] = 'A név kötelező, legfeljebb 120 karakter.';
    }
    if (!in_array($clean['category_id'], $categoryIds, true)) {
        $errors['category_id'] = 'Válasszon kategóriát.';
    }
    if ($clean['base_price'] === false || $clean['base_price'] < 0 || $clean['base_price'] > 1000000) {
        $errors['base_price'] = 'Az alapár 0 és 1 000 000 Ft közötti egész szám.';
    }
    if ($clean['description'] === '' || mb_strlen($clean['description']) > 2000) {
        $errors['description'] = 'A leírás kötelező, legfeljebb 2000 karakter.';
    }
    if ($clean['image'] !== '' && !in_array($clean['image'], $images, true)) {
        $errors['image'] = 'A kiválasztott kép nem található az img/products mappában.';
    }

    [$clean['variants'], $variantError] = validate_variants($input['variants'] ?? [], (int) $clean['base_price']);
    if ($variantError !== null) {
        $errors['variants'] = $variantError;
    }

    return [$clean, $errors];
}

/**
 * @return array{0: array, 1: ?string} a változatok és az első hiba
 */
function validate_variants(mixed $rows, int $basePrice): array
{
    $variants = [];
    $seen = [];

    foreach (is_array($rows) ? $rows : [] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $color = trim(input_string($row, 'color'));
        $size = trim(input_string($row, 'size'));
        $stockInput = trim(input_string($row, 'stock'));
        $diffInput = trim(input_string($row, 'price_diff'));

        if ($color === '' && $size === '' && $stockInput === '' && $diffInput === '') {
            continue;
        }

        $stock = filter_var($stockInput, FILTER_VALIDATE_INT);
        $diff = $diffInput === '' ? 0 : filter_var($diffInput, FILTER_VALIDATE_INT);
        $hex = input_string($row, 'color_hex');

        if (mb_strlen($color) > 40 || mb_strlen($size) > 40) {
            return [[], 'A szín és a méret legfeljebb 40 karakter lehet.'];
        }
        if ($stock === false || $stock < 0 || $stock > 100000) {
            return [[], 'A készlet 0 és 100 000 közötti egész szám legyen minden változatnál.'];
        }
        if ($diff === false || abs($diff) > 1000000 || $basePrice + $diff < 0) {
            return [[], 'Az árkülönbség egész szám legyen, és az alapárral együtt nem lehet negatív.'];
        }
        if ($color !== '' && !preg_match(HEX_COLOR_PATTERN, $hex)) {
            return [[], 'Érvénytelen színkód az egyik változatnál.'];
        }

        $key = mb_strtolower($color . '|' . $size);
        if (isset($seen[$key])) {
            return [[], 'Két változat színe és mérete megegyezik.'];
        }
        $seen[$key] = true;

        $variants[] = [
            'id' => (int) ($row['id'] ?? 0),
            'color' => $color !== '' ? $color : null,
            'color_hex' => $color !== '' ? strtolower($hex) : null,
            'size' => $size !== '' ? $size : null,
            'price_diff' => $diff,
            'stock' => $stock,
        ];
    }

    if ($variants === []) {
        return [[], 'Legalább egy változatot (készlettel) meg kell adni.'];
    }

    // A termékoldal csak akkor tud választót építeni, ha minden változatnak van színe (vagy egynek sincs)
    $withColor = count(array_filter($variants, static fn ($v) => $v['color'] !== null));
    $withSize = count(array_filter($variants, static fn ($v) => $v['size'] !== null));
    if (($withColor !== 0 && $withColor !== count($variants)) || ($withSize !== 0 && $withSize !== count($variants))) {
        return [[], 'A szín és a méret mezőt vagy minden változatnál ki kell tölteni, vagy egynél sem.'];
    }

    return [$variants, null];
}

function unique_slug(PDO $pdo, string $name): string
{
    $base = slugify($name);
    $slug = $base;
    $suffix = 2;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE slug = :slug');

    while (true) {
        $stmt->execute(['slug' => $slug]);
        if ((int) $stmt->fetchColumn() === 0) {
            return $slug;
        }
        $slug = $base . '-' . $suffix++;
    }
}

/**
 * Új termék felvitele vagy meglévő módosítása a változataival együtt. A termék azonosítóját adja vissza.
 */
function save_product(PDO $pdo, array $data, int $id = 0): int
{
    $pdo->beginTransaction();

    try {
        $fields = [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'image' => $data['image'] !== '' ? $data['image'] : null,
            'base_price' => $data['base_price'],
            'is_featured' => $data['is_featured'],
            'is_active' => $data['is_active'],
        ];

        if ($id === 0) {
            $fields['slug'] = unique_slug($pdo, $data['name']);
            $stmt = $pdo->prepare(
                'INSERT INTO products (category_id, name, slug, description, image, base_price, is_featured, is_active)
                 VALUES (:category_id, :name, :slug, :description, :image, :base_price, :is_featured, :is_active)'
            );
            $stmt->execute($fields);
            $id = (int) $pdo->lastInsertId();
        } else {
            $fields['id'] = $id;
            $stmt = $pdo->prepare(
                'UPDATE products SET category_id = :category_id, name = :name, description = :description, image = :image,
                        base_price = :base_price, is_featured = :is_featured, is_active = :is_active
                 WHERE id = :id'
            );
            $stmt->execute($fields);
        }

        save_variants($pdo, $id, $data['variants']);
        $pdo->commit();

        return $id;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * A meglévő változatokat frissíti (a rendelések hivatkozása így megmarad), az újakat felveszi, a kimaradtakat törli.
 */
function save_variants(PDO $pdo, int $productId, array $variants): void
{
    $stmt = $pdo->prepare('SELECT id FROM product_variants WHERE product_id = :id');
    $stmt->execute(['id' => $productId]);
    $existingIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));

    $update = $pdo->prepare(
        'UPDATE product_variants SET color = :color, color_hex = :color_hex, size = :size, price_diff = :price_diff, stock = :stock
         WHERE id = :id AND product_id = :product_id'
    );
    $insert = $pdo->prepare(
        'INSERT INTO product_variants (product_id, color, color_hex, size, price_diff, stock)
         VALUES (:product_id, :color, :color_hex, :size, :price_diff, :stock)'
    );

    $keptIds = [];
    foreach ($variants as $variant) {
        $params = [
            'product_id' => $productId,
            'color' => $variant['color'],
            'color_hex' => $variant['color_hex'],
            'size' => $variant['size'],
            'price_diff' => $variant['price_diff'],
            'stock' => $variant['stock'],
        ];

        if (in_array($variant['id'], $existingIds, true)) {
            $update->execute($params + ['id' => $variant['id']]);
            $keptIds[] = $variant['id'];
        } else {
            $insert->execute($params);
        }
    }

    $delete = $pdo->prepare('DELETE FROM product_variants WHERE id = :id');
    foreach (array_diff($existingIds, $keptIds) as $removedId) {
        $delete->execute(['id' => $removedId]);
    }
}
