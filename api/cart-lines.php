<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/orders.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_post()) {
    http_response_code(405);
    echo json_encode(['error' => 'Csak POST kérés engedélyezett.']);
    exit;
}

$items = decode_cart_json((string) file_get_contents('php://input'));
if ($items === null) {
    http_response_code(400);
    echo json_encode(['error' => 'A kosár adatai hibás formátumúak.']);
    exit;
}

$cart = parse_cart_input($items);

try {
    $lines = fetch_cart_lines(db(), $cart);
} catch (PDOException $e) {
    error_log('Kosár lekérdezési hiba: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'A kosár adatai most nem érhetők el.']);
    exit;
}

echo json_encode([
    'lines' => array_map(static fn (array $line): array => [
        'variantId' => $line['variant_id'],
        'productName' => $line['product_name'],
        'label' => $line['label'],
        'price' => $line['price'],
        'stock' => $line['stock'],
        'quantity' => $line['quantity'],
        'available' => $line['available'],
        'image' => product_image_url($line['image'], $line['color_hex']),
        'url' => 'product.php?slug=' . urlencode($line['slug']),
    ], $lines),
], JSON_UNESCAPED_UNICODE);
