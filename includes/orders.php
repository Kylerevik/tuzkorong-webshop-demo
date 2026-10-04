<?php
declare(strict_types=1);

class OrderException extends RuntimeException
{
}

/**
 * A böngészőből érkező kosár JSON-ja; null, ha nem tömb formátumú.
 */
function decode_cart_json(string $json): ?array
{
    $items = json_decode($json, true);

    return is_array($items) && array_is_list($items) ? $items : null;
}

/**
 * A kosár tételeit [variáns azonosító => mennyiség] tömbbé alakítja, az érvénytelen sorokat eldobja.
 */
function parse_cart_input(array $items): array
{
    $cart = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $variantId = filter_var($item['variantId'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
        if ($variantId === false || $variantId < 1 || $quantity === false || $quantity < 1) {
            continue;
        }
        $cart[$variantId] = min(99, ($cart[$variantId] ?? 0) + $quantity);
    }

    return array_slice($cart, 0, 50, true);
}

/**
 * Az árakat és a készletet mindig az adatbázisból olvassa. Zárolással a rendelés
 * közbeni egyidejű vásárlás sem tud túlfogyasztani.
 */
function fetch_cart_lines(PDO $pdo, array $cart, bool $lock = false): array
{
    if ($cart === []) {
        return [];
    }

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT v.id, v.color, v.color_hex, v.size, v.stock, p.name, p.slug, p.image, p.is_active,
                   p.base_price + v.price_diff AS price
            FROM product_variants v
            JOIN products p ON p.id = v.product_id
            WHERE v.id IN ($placeholders)" . ($lock ? ' FOR UPDATE' : '');

    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);

    $rows = [];
    foreach ($stmt->fetchAll() as $row) {
        $rows[(int) $row['id']] = $row;
    }

    $lines = [];
    foreach ($cart as $variantId => $quantity) {
        if (!isset($rows[$variantId])) {
            continue;
        }
        $row = $rows[$variantId];
        $lines[] = [
            'variant_id' => $variantId,
            'product_name' => $row['name'],
            'slug' => $row['slug'],
            'image' => $row['image'],
            'color_hex' => $row['color_hex'],
            'label' => variant_label($row['color'], $row['size']),
            'price' => (int) $row['price'],
            'stock' => (int) $row['stock'],
            'quantity' => $quantity,
            'available' => (int) $row['is_active'] === 1 && (int) $row['stock'] > 0,
        ];
    }

    return $lines;
}

function shipping_cost(array $method, int $subtotal): int
{
    $freeFrom = $method['free_from'];

    if ($freeFrom !== null && $subtotal >= (int) $freeFrom) {
        return 0;
    }

    return (int) $method['price'];
}

/**
 * @return array{0: array, 1: array} a megtisztított adatok és a hibák mezőnként
 */
function validate_checkout(array $input, array $shippingMethods): array
{
    $clean = [
        'name' => trim(input_string($input, 'name')),
        'phone' => trim(input_string($input, 'phone')),
        'zip' => trim(input_string($input, 'zip')),
        'city' => trim(input_string($input, 'city')),
        'street' => trim(input_string($input, 'street')),
        'note' => trim(input_string($input, 'note')),
        'shipping_method_id' => (int) ($input['shipping_method_id'] ?? 0),
    ];
    $errors = [];

    $method = $shippingMethods[$clean['shipping_method_id']] ?? null;
    if ($method === null) {
        $errors['shipping_method_id'] = 'Válasszon szállítási módot.';
    }

    $nameLength = mb_strlen($clean['name']);
    if ($nameLength < 3 || $nameLength > 100) {
        $errors['name'] = 'Adja meg a teljes nevét (legalább 3 karakter).';
    }
    if (!preg_match('/^\+?[0-9 ()\/-]{7,20}$/', $clean['phone'])) {
        $errors['phone'] = 'Adjon meg érvényes telefonszámot, például +36 30 555 0123.';
    }
    if (mb_strlen($clean['note']) > 500) {
        $errors['note'] = 'A megjegyzés legfeljebb 500 karakter lehet.';
    }

    if ($method !== null && (int) $method['requires_address'] === 1) {
        if (!preg_match('/^\d{4}$/', $clean['zip'])) {
            $errors['zip'] = 'Az irányítószám négy számjegy.';
        }
        if (mb_strlen($clean['city']) < 2 || mb_strlen($clean['city']) > 60) {
            $errors['city'] = 'Adja meg a települést.';
        }
        if (mb_strlen($clean['street']) < 3 || mb_strlen($clean['street']) > 120) {
            $errors['street'] = 'Adja meg az utcát és a házszámot.';
        }
    } else {
        $clean['zip'] = $clean['city'] = $clean['street'] = '';
    }

    return [$clean, $errors];
}

/**
 * Rögzíti a rendelést, és levonja a készletet egy tranzakcióban.
 */
function place_order(PDO $pdo, array $customer, array $cart, array $method): int
{
    $pdo->beginTransaction();

    try {
        $lines = fetch_cart_lines($pdo, $cart, true);

        if (count($lines) !== count($cart)) {
            throw new OrderException('A kosárban lévő egyik termék már nem kapható. Kérjük, ellenőrizze a kosarát.');
        }

        $subtotal = 0;
        foreach ($lines as $line) {
            if (!$line['available'] || $line['quantity'] > $line['stock']) {
                throw new OrderException(
                    'Sajnos a(z) ' . $line['product_name'] . ' (' . $line['label'] . ') készlete közben megváltozott. Kérjük, frissítse a kosarát.'
                );
            }
            $subtotal += $line['price'] * $line['quantity'];
        }

        $shipping = shipping_cost($method, $subtotal);

        $insertOrder = $pdo->prepare(
            'INSERT INTO orders (customer_name, phone, zip, city, street, note, shipping_method, shipping_cost, subtotal, total)
             VALUES (:name, :phone, :zip, :city, :street, :note, :shipping_method, :shipping_cost, :subtotal, :total)'
        );
        $insertOrder->execute([
            'name' => $customer['name'],
            'phone' => $customer['phone'],
            'zip' => $customer['zip'] ?: null,
            'city' => $customer['city'] ?: null,
            'street' => $customer['street'] ?: null,
            'note' => $customer['note'] ?: null,
            'shipping_method' => $method['name'],
            'shipping_cost' => $shipping,
            'subtotal' => $subtotal,
            'total' => $subtotal + $shipping,
        ]);
        $orderId = (int) $pdo->lastInsertId();

        $insertItem = $pdo->prepare(
            'INSERT INTO order_items (order_id, variant_id, product_name, variant_label, unit_price, quantity)
             VALUES (:order_id, :variant_id, :product_name, :variant_label, :unit_price, :quantity)'
        );
        $reduceStock = $pdo->prepare('UPDATE product_variants SET stock = stock - :quantity WHERE id = :id');

        foreach ($lines as $line) {
            $insertItem->execute([
                'order_id' => $orderId,
                'variant_id' => $line['variant_id'],
                'product_name' => $line['product_name'],
                'variant_label' => $line['label'],
                'unit_price' => $line['price'],
                'quantity' => $line['quantity'],
            ]);
            $reduceStock->execute(['quantity' => $line['quantity'], 'id' => $line['variant_id']]);
        }

        $pdo->commit();

        return $orderId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
