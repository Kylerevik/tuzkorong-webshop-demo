<?php
require __DIR__ . '/includes/bootstrap.php';

$categories = fetch_categories();
$categorySlugs = array_column($categories, 'slug');

$selectedCategory = input_string($_GET, 'category');
if (!in_array($selectedCategory, $categorySlugs, true)) {
    $selectedCategory = '';
}

$sort = input_string($_GET, 'sort');
if (!isset(PRODUCT_SORTS[$sort])) {
    $sort = 'default';
}

$products = fetch_products($selectedCategory !== '' ? ['category_slug' => $selectedCategory] : [], $sort);

$selectedName = '';
foreach ($categories as $category) {
    if ($category['slug'] === $selectedCategory) {
        $selectedName = $category['name'];
    }
}

$pageTitle = $selectedName !== '' ? $selectedName : 'Termékek';
$pageDescription = $selectedName !== ''
    ? $selectedName . ' a Tűzkorong műhelyből: kézzel korongozott, mosogatógépben mosható kerámia, több színben.'
    : 'Bögrék, tányérok, vázák és ajándékcsomagok a pécsi Tűzkorong műhelyből. Szűrjön kategóriára, rendezze ár vagy név szerint.';
$activePage = 'products';
$pageScripts = ['js/products.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <nav aria-label="Morzsamenü">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Főoldal</a></li>
                <li class="breadcrumb-item<?= $selectedName === '' ? ' active' : '' ?>"><?= $selectedName === '' ? 'Termékek' : '<a href="products.php">Termékek</a>' ?></li>
                <?php if ($selectedName !== ''): ?><li class="breadcrumb-item active" aria-current="page"><?= e($selectedName) ?></li><?php endif; ?>
            </ol>
        </nav>

        <h1 class="section__title"><?= e($pageTitle) ?></h1>

        <div class="shop-toolbar">
            <ul class="filter-list" aria-label="Kategóriák">
                <li><a class="filter-pill<?= $selectedCategory === '' ? ' is-active' : '' ?>" href="products.php?sort=<?= e($sort) ?>">Összes</a></li>
                <?php foreach ($categories as $category): ?>
                <li>
                    <a class="filter-pill<?= $selectedCategory === $category['slug'] ? ' is-active' : '' ?>"
                       href="products.php?category=<?= e($category['slug']) ?>&amp;sort=<?= e($sort) ?>"><?= e($category['name']) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <form class="sort-form" method="get" action="products.php" data-sort-form>
                <?php if ($selectedCategory !== ''): ?><input type="hidden" name="category" value="<?= e($selectedCategory) ?>"><?php endif; ?>
                <label class="form-label mb-0" for="sort">Rendezés</label>
                <select class="form-select" id="sort" name="sort">
                    <?php foreach (PRODUCT_SORTS as $key => $option): ?>
                    <option value="<?= e($key) ?>"<?= $key === $sort ? ' selected' : '' ?>><?= e($option['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <noscript><button class="btn btn-outline-secondary" type="submit">Alkalmaz</button></noscript>
            </form>
        </div>

        <p class="text-muted" role="status"><?= count($products) ?> termék</p>

        <?php if ($products === []): ?>
        <p>Ebben a kategóriában jelenleg nincs elérhető termék. Nézzen vissza később, vagy <a href="products.php">böngéssze az összes darabot</a>.</p>
        <?php else: ?>
        <h2 class="visually-hidden">Terméklista</h2>
        <div class="row g-3 g-lg-4">
            <?php foreach ($products as $product): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <?php require __DIR__ . '/includes/product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
