<?php
require __DIR__ . '/includes/bootstrap.php';

$categories = fetch_categories();
$featured = fetch_products(['featured' => true], 'default', 6);

$pageDescription = 'Kézzel korongozott bögrék, tányérok és vázák a pécsi Tűzkorong műhelyből. Rendeljen online, vagy vegye át személyesen a műhelyben.';
$activePage = 'home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <p class="eyebrow">Pécsi kerámiaműhely, 2018 óta</p>
                <h1 class="hero__title">Korongon húzott kerámia, ami a mindennapokban is szép</h1>
                <p class="hero__lead">A Tűzkorong műhelyben minden bögrét, tányért és vázát kézzel készítünk, kőedény agyagból, ólommentes mázzal. A webshopban azok a darabok vannak, amelyek éppen raktáron várnak.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-primary btn-lg" href="products.php">Termékek megtekintése</a>
                    <a class="btn btn-outline-secondary btn-lg" href="#muhely">A műhelyről</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img class="hero__image" src="img/hero.svg" width="640" height="520" alt="Kézzel készített váza, bögre és karcsú váza egy fa polcon">
            </div>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="categories-title">
    <div class="container">
        <h2 id="categories-title" class="section__title">Böngésszen kategóriák szerint</h2>
        <div class="row g-3">
            <?php foreach ($categories as $category): ?>
            <div class="col-6 col-md-4 col-lg">
                <a class="category-tile" href="products.php?category=<?= e($category['slug']) ?>">
                    <img src="<?= e(product_image_url($category['image'])) ?>" width="400" height="400" loading="lazy" alt="">
                    <span class="category-tile__name"><?= e($category['name']) ?></span>
                    <span class="category-tile__count"><?= (int) $category['product_count'] ?> termék</span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--tinted" aria-labelledby="featured-title">
    <div class="container">
        <div class="section__head">
            <h2 id="featured-title" class="section__title">Kiemelt darabjaink</h2>
            <a href="products.php">Az összes termék</a>
        </div>
        <div class="row g-3 g-lg-4">
            <?php foreach ($featured as $product): ?>
            <div class="col-6 col-lg-4">
                <?php require __DIR__ . '/includes/product-card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" id="muhely" aria-labelledby="workshop-title">
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-6">
                <h2 id="workshop-title" class="section__title">Egy kis műhely a Mecsek lábánál</h2>
                <p>A Tűzkorong a pécsi belváros mellett működik, két fazekassal. Minden edényt korongon húzunk, kézzel simítunk, majd kétszer égetünk: egyszer nyersen, egyszer mázzal.</p>
                <p>Ezért nincs két teljesen egyforma darab. A máz árnyalata, a perem vonala és a fül formája kicsit eltér, és ez így van rendjén. Ha egy adott színből van raktáron, a képen látottal megegyező minőséget kap.</p>
                <p class="mb-0">Egyedi méretben vagy színben is vállalunk megrendelést, ilyenkor hívjon minket a műhely nyitvatartási idejében.</p>
            </div>
            <div class="col-lg-6">
                <dl class="facts">
                    <div class="facts__item">
                        <dt>1 240 °C</dt>
                        <dd>a mázas égetés hőmérséklete, ezért az edények mosogatógépben is bírják</dd>
                    </div>
                    <div class="facts__item">
                        <dt>100%</dt>
                        <dd>kézzel korongozva, öntött darab nincs a kínálatban</dd>
                    </div>
                    <div class="facts__item">
                        <dt>2 hét</dt>
                        <dd>telik el átlagosan a nyers agyagtól a kész edényig</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</section>

<section class="section section--tinted" id="szallitas" aria-labelledby="shipping-title">
    <div class="container">
        <h2 id="shipping-title" class="section__title">Szállítás és átvétel</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="info-card">
                    <h3 class="info-card__title">Futárszolgálat</h3>
                    <p class="mb-0">Munkanapokon 2-3 napon belül házhoz visszük. 20 000 Ft feletti rendelésnél a szállítás ingyenes, alatta 1 490 Ft. Másnapra is kérheti expressz futárral.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <h3 class="info-card__title">Biztonságos csomagolás</h3>
                    <p class="mb-0">Újrahasznosított papírban és hullámkartonban küldjük, műanyag nélkül. Ha mégis eltörne valami úton, fényképet kérünk, és pótoljuk a darabot.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-card">
                    <h3 class="info-card__title">Átvétel a műhelyben</h3>
                    <p class="mb-0">Pécs, Tímár utca 11. Hétfőtől péntekig 10 és 17 óra között, szombaton 9 és 13 óra között. A rendelés felvétele után telefonon jelezzük, ha készen áll.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
