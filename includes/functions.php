<?php
declare(strict_types=1);

const ORDER_STATUSES = [
    'new' => 'Új',
    'processing' => 'Feldolgozás alatt',
    'shipped' => 'Kiszállítva',
];

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function format_price(int $amount): string
{
    return number_format($amount, 0, '', "\u{00A0}") . "\u{00A0}Ft";
}

function order_number(int $orderId): string
{
    return 'TK-' . str_pad((string) $orderId, 5, '0', STR_PAD_LEFT);
}

function variant_label(?string $color, ?string $size): string
{
    return implode(' / ', array_filter([$color, $size], static fn ($part) => $part !== null && $part !== ''));
}

/**
 * Az SVG termékképek a kiválasztott szín máz színével jönnek (img.php), a fotók változatlanok maradnak.
 */
function product_image_url(?string $image, ?string $colorHex = null): string
{
    if ($image !== null && $colorHex !== null && str_ends_with($image, '.svg') && preg_match('/^#[0-9a-fA-F]{6}$/', $colorHex)) {
        return 'img.php?f=' . rawurlencode($image) . '&color=' . substr($colorHex, 1);
    }

    return 'img/products/' . ($image ?: 'placeholder.svg');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function slugify(string $text): string
{
    $map = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ö' => 'o', 'ő' => 'o', 'ú' => 'u', 'ü' => 'u', 'ű' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ö' => 'o', 'Ő' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ű' => 'u',
    ];
    $slug = strtolower(strtr($text, $map));
    $slug = trim(preg_replace('/[^a-z0-9]+/', '-', $slug), '-');

    return $slug !== '' ? $slug : 'termek';
}

/**
 * Szöveges kérésparaméter; tömbként vagy más típusként érkező értékből üres szöveg lesz.
 */
function input_string(array $source, string $key): string
{
    $value = $source[$key] ?? '';

    return is_string($value) ? $value : '';
}

function input_int(array $source, string $key): int
{
    $value = filter_var($source[$key] ?? null, FILTER_VALIDATE_INT);

    return $value === false ? 0 : $value;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $sent = $_POST['csrf_token'] ?? '';

    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Érvénytelen kérés. Töltse újra az oldalt, és próbálja meg újra.');
    }
}

function set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $flash;
}

function field_class(array $errors, string $field): string
{
    return 'form-control' . (isset($errors[$field]) ? ' is-invalid' : '');
}

function field_error(array $errors, string $field): string
{
    return isset($errors[$field]) ? '<div class="invalid-feedback">' . e($errors[$field]) . '</div>' : '';
}
