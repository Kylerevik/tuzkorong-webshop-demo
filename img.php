<?php
declare(strict_types=1);

// Termék SVG kiszolgálása a kért máz színével: img.php?f=mecsek-bogre.svg&color=7f8f69
$file = (string) ($_GET['f'] ?? '');
$color = (string) ($_GET['color'] ?? '');
$path = __DIR__ . '/img/products/' . $file;

if (!preg_match('/^[a-z0-9-]+\.svg$/', $file) || !is_file($path)) {
    http_response_code(404);
    exit;
}

$svg = (string) file_get_contents($path);

if (preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
    $svg = preg_replace('/--glaze:\s*#[0-9a-fA-F]{6}/', '--glaze:#' . strtolower($color), $svg, 1);
}

header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=604800');
echo $svg;
