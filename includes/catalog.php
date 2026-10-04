<?php
declare(strict_types=1);

const PRODUCT_SORTS = [
    'default' => ['label' => 'Ajánlott', 'sql' => 'p.is_featured DESC, p.name ASC'],
    'price_asc' => ['label' => 'Ár szerint növekvő', 'sql' => 'price_from ASC, p.name ASC'],
    'price_desc' => ['label' => 'Ár szerint csökkenő', 'sql' => 'price_from DESC, p.name ASC'],
    'name_asc' => ['label' => 'Név szerint A-Z', 'sql' => 'p.name ASC'],
    'name_desc' => ['label' => 'Név szerint Z-A', 'sql' => 'p.name DESC'],
];

function fetch_categories(): array
{
    $sql = 'SELECT c.id, c.slug, c.name,
                   COUNT(p.id) AS product_count,
                   (SELECT p2.image FROM products p2
                     WHERE p2.category_id = c.id AND p2.is_active = 1
                     ORDER BY p2.is_featured DESC, p2.id ASC LIMIT 1) AS image
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
            GROUP BY c.id, c.slug, c.name
            ORDER BY c.id';

    return db()->query($sql)->fetchAll();
}

/**
 * Aktív termékek listája "-tól" árral. A szűrők: category_slug, featured, exclude_id.
 */
function fetch_products(array $filter = [], string $sort = 'default', ?int $limit = null): array
{
    $where = ['p.is_active = 1'];
    $params = [];

    if (!empty($filter['category_slug'])) {
        $where[] = 'c.slug = :category_slug';
        $params['category_slug'] = $filter['category_slug'];
    }
    if (!empty($filter['category_id'])) {
        $where[] = 'p.category_id = :category_id';
        $params['category_id'] = $filter['category_id'];
    }
    if (!empty($filter['featured'])) {
        $where[] = 'p.is_featured = 1';
    }
    if (!empty($filter['exclude_id'])) {
        $where[] = 'p.id <> :exclude_id';
        $params['exclude_id'] = $filter['exclude_id'];
    }

    $orderBy = (PRODUCT_SORTS[$sort] ?? PRODUCT_SORTS['default'])['sql'];
    $limitSql = $limit !== null ? ' LIMIT ' . $limit : '';

    // A "-tól" ár a készleten lévő változatok legolcsóbbja, ha nincs készlet, az összes közül
    $sql = 'SELECT p.id, p.name, p.slug, p.image, c.name AS category_name,
                   p.base_price + COALESCE(MIN(CASE WHEN v.stock > 0 THEN v.price_diff END), MIN(v.price_diff), 0) AS price_from,
                   COALESCE(SUM(v.stock), 0) AS total_stock,
                   (MIN(v.price_diff) <> MAX(v.price_diff)) AS has_price_range
            FROM products p
            JOIN categories c ON c.id = p.category_id
            LEFT JOIN product_variants v ON v.product_id = p.id
            WHERE ' . implode(' AND ', $where) . '
            GROUP BY p.id, p.name, p.slug, p.image, p.base_price, p.is_featured, c.name
            ORDER BY ' . $orderBy . $limitSql;

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function fetch_product_by_slug(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT p.id, p.category_id, p.name, p.slug, p.description, p.image, p.base_price, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.slug = :slug AND p.is_active = 1'
    );
    $stmt->execute(['slug' => $slug]);

    return $stmt->fetch() ?: null;
}

function fetch_variants(int $productId): array
{
    $stmt = db()->prepare(
        'SELECT v.id, v.color, v.color_hex, v.size, v.stock, p.base_price + v.price_diff AS price
         FROM product_variants v
         JOIN products p ON p.id = v.product_id
         WHERE v.product_id = :product_id
         ORDER BY v.id'
    );
    $stmt->execute(['product_id' => $productId]);

    return $stmt->fetchAll();
}

function fetch_shipping_methods(): array
{
    return db()->query('SELECT id, name, description, price, free_from, requires_address FROM shipping_methods ORDER BY id')->fetchAll();
}
