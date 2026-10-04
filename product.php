<?php
require __DIR__ . '/includes/bootstrap.php';

$product = fetch_product_by_slug((string) ($_GET['slug'] ?? ''));

if ($product === null) {
    http_response_code(404);
    $pageTitle = 'A termék nem található';
    $activePage = 'products';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container"><h1 class="section__title">A termék nem található</h1>'
        . '<p>Lehet, hogy elfogyott és kivettük a kínálatból, vagy elgépelték a címet. '
        . '<a href="products.php">Nézze meg a teljes kínálatot</a>.</p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$variants = fetch_variants((int) $product['id']);
$variantData = array_map(static fn (array $v): array => [
    'id' => (int) $v['id'],
    'color' => $v['color'],
    'colorHex' => $v['color_hex'],
    'size' => $v['size'],
    'stock' => (int) $v['stock'],
    'price' => (int) $v['price'],
], $variants);

$prices = array_column($variantData, 'price');
$initial = null;
foreach ($variantData as $variant) {
    if ($initial === null || ($initial['stock'] <= 0 && $variant['stock'] > 0)) {
        $initial = $variant;
    }
}
$hasColors = $variants !== [] && $variants[0]['color'] !== null;
$hasSizes = $variants !== [] && $variants[0]['size'] !== null;
$inStock = array_sum(array_column($variantData, 'stock')) > 0;
$related = fetch_products(['category_id' => (int) $product['category_id'], 'exclude_id' => (int) $product['id']], 'default', 4);

$pageTitle = $product['name'];
$pageDescription = mb_strimwidth($product['description'], 0, 155, '...');
$activePage = 'products';
$pageScripts = ['js/product.js'];

$structuredData = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product['name'],
    'description' => $product['description'],
    'category' => $product['category_name'],
    'brand' => ['@type' => 'Brand', 'name' => 'Tűzkorong'],
    'offers' => [
        '@type' => 'AggregateOffer',
        'priceCurrency' => 'HUF',
        'lowPrice' => $prices !== [] ? min($prices) : (int) $product['base_price'],
        'highPrice' => $prices !== [] ? max($prices) : (int) $product['base_price'],
        'availability' => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ],
];
$extraHead = '    <script type="application/ld+json">'
    . json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)
    . "</script>\n";

require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <nav aria-label="Morzsamenü">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Főoldal</a></li>
                <li class="breadcrumb-item"><a href="products.php">Termékek</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($product['name']) ?></li>
            </ol>
        </nav>

        <div class="product-detail row g-4 g-lg-5"
             data-product-detail
             data-product-name="<?= e($product['name']) ?>"
             data-variants="<?= e(json_encode($variantData, JSON_UNESCAPED_UNICODE)) ?>">
            <div class="col-md-6">
                <div class="product-detail__image">
                    <img src="<?= e(product_image_url($product['image'], $initial['colorHex'] ?? null)) ?>" width="400" height="400" alt="<?= e($product['name']) ?>"
                         data-product-image<?= $product['image'] !== null && str_ends_with($product['image'], '.svg') ? ' data-image-file="' . e($product['image']) . '"' : '' ?>>
                </div>
            </div>
            <div class="col-md-6">
                <p class="product-card__category"><?= e($product['category_name']) ?></p>
                <h1 class="product-detail__title"><?= e($product['name']) ?></h1>
                <p class="product-detail__price" data-price><?= $prices !== [] ? format_price((int) min($prices)) : '' ?></p>
                <p class="product-detail__description"><?= e($product['description']) ?></p>

                <?php if ($variants === []): ?>
                <p class="text-muted">Ez a termék jelenleg nem rendelhető.</p>
                <?php else: ?>
                <?php if ($hasColors): ?>
                <fieldset class="option-group">
                    <legend class="option-group__title">Szín: <strong data-selected="color"></strong></legend>
                    <div class="option-list" data-options="color"></div>
                </fieldset>
                <?php endif; ?>
                <?php if ($hasSizes): ?>
                <fieldset class="option-group">
                    <legend class="option-group__title">Méret: <strong data-selected="size"></strong></legend>
                    <div class="option-list" data-options="size"></div>
                </fieldset>
                <?php endif; ?>

                <p class="stock-status" data-stock-status></p>

                <div class="buy-row">
                    <div class="quantity">
                        <button class="quantity__button" type="button" data-quantity-step="-1" aria-label="Kevesebb">&minus;</button>
                        <input class="quantity__input" type="number" min="1" max="1" value="1" inputmode="numeric" aria-label="Mennyiség" data-quantity>
                        <button class="quantity__button" type="button" data-quantity-step="1" aria-label="Több">+</button>
                    </div>
                    <button class="btn btn-primary btn-lg flex-grow-1" type="button" data-add-to-cart>Kosárba</button>
                </div>
                <p class="cart-message" role="status" data-message></p>
                <?php endif; ?>

                <ul class="care-list">
                    <li>Kézzel korongozva, minden darab egyedi</li>
                    <li>Mosogatógépben és mikrohullámú sütőben is használható</li>
                    <li>Ólommentes máz, élelmiszerrel érintkezhet</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php if ($related !== []): ?>
<section class="section section--tinted" aria-labelledby="related-title">
    <div class="container">
        <h2 id="related-title" class="section__title">Ez is érdekelheti</h2>
        <div class="row g-3 g-lg-4">
            <?php foreach ($related as $product): ?>
            <div class="col-6 col-lg-3">
                <?php require __DIR__ . '/includes/product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
