<?php
// Egy termék kártyája a listákban. Bemenet: $product (fetch_products egy sora).
$productUrl = 'product.php?slug=' . urlencode($product['slug']);
$soldOut = (int) $product['total_stock'] <= 0;
?>
<article class="product-card">
    <a class="product-card__image" href="<?= e($productUrl) ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= e(product_image_url($product['image'])) ?>" width="400" height="400" loading="lazy" alt="">
        <?php if ($soldOut): ?><span class="product-card__badge">Elfogyott</span><?php endif; ?>
    </a>
    <div class="product-card__body">
        <p class="product-card__category"><?= e($product['category_name']) ?></p>
        <h3 class="product-card__name"><a href="<?= e($productUrl) ?>"><?= e($product['name']) ?></a></h3>
        <p class="product-card__price">
            <?= format_price((int) $product['price_from']) ?><?= (int) $product['has_price_range'] === 1 ? '-tól' : '' ?>
        </p>
    </div>
</article>
