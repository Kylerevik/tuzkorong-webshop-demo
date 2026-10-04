-- Tűzkorong Kerámiaműhely demó adatbázis
-- Importálás előtt hozz létre egy utf8mb4_unicode_ci adatbázist, és válaszd ki.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS shipping_methods;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS admins;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(60) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    image VARCHAR(120) NULL,
    base_price INT NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_variants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    color VARCHAR(40) NULL,
    color_hex CHAR(7) NULL,
    size VARCHAR(40) NULL,
    price_diff INT NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shipping_methods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(160) NOT NULL,
    price INT NOT NULL,
    free_from INT NULL,
    requires_address TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    zip CHAR(4) NULL,
    city VARCHAR(60) NULL,
    street VARCHAR(120) NULL,
    note TEXT NULL,
    shipping_method VARCHAR(80) NOT NULL,
    shipping_cost INT NOT NULL,
    subtotal INT NOT NULL,
    total INT NOT NULL,
    status ENUM('new', 'processing', 'shipped') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NULL,
    product_name VARCHAR(120) NOT NULL,
    variant_label VARCHAR(100) NOT NULL,
    unit_price INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin belépés: admin / Agyag2026
INSERT INTO admins (username, password_hash) VALUES ('admin', '$2y$10$IiVG0Juz1AmpL4wmhBQ5VOX0OKsAr26KL72PK3WToTM8Le9Y8m/uC');

INSERT INTO categories (slug, name) VALUES
    ('bogre', 'Bögrék és csészék'),
    ('tanyer', 'Tányérok és tálak'),
    ('vaza', 'Vázák'),
    ('kiegeszito', 'Kiegészítők'),
    ('ajandekcsomag', 'Ajándékcsomagok');

INSERT INTO products (category_id, name, slug, description, image, base_price, is_featured) VALUES
    (1, 'Mecsek bögre', 'mecsek-bogre', 'Egyenes falú, jól fogó bögre kézzel húzott füllel. Korongon készül, kétszer égetjük, ezért a máz árnyalata darabonként kicsit eltér. Mosogatógépben és mikrohullámú sütőben is használható.', 'mecsek-bogre.svg', 3900, 1),
    (1, 'Eszpresszócsésze alj-tányérral', 'eszpresszocsesze', 'Vastag falú, sokáig melegen tartó csésze a hozzá illő alj-tányérral. A csésze 90 ml-es, az alj-tányér átmérője 12 cm.', 'eszpresszocsesze.svg', 4500, 0),
    (1, 'Fül nélküli teáscsésze', 'ful-nelkuli-teascsesze', 'Fül nélküli, tenyérbe simuló teáscsésze. A lábgyűrű miatt az asztalt sem melegíti át, a keze pedig kényelmesen körbeéri.', 'ful-nelkuli-teascsesze.svg', 3400, 0),
    (2, 'Korong lapostányér', 'korong-lapostanyer', 'Egyszerű, mindennapi használatra szánt lapostányér. A perem finoman felemelkedik, így a szósz nem csúszik le róla. Mosogatógépben mosható.', 'korong-lapostanyer.svg', 4800, 1),
    (2, 'Mély tányér', 'mely-tanyer', 'Levesnek, tésztának, müzlinek is jó. A vastagabb fal sokáig melegen tartja az ételt, a széles perem pedig jól fogható.', 'mely-tanyer.svg', 4200, 0),
    (2, 'Nagy tálalótál', 'nagy-talalotal', 'Salátának, tésztának vagy gyümölcsnek. A belseje világos, a külseje a választott színben mázas. Két méretben kapható.', 'nagy-talalotal.svg', 9800, 1),
    (2, 'Szósztálka', 'szosztalka', 'Kicsi tálka szósznak, mártogatósnak vagy olajbogyónak. Egymásba is rakható, ezért az asztalnál sem foglal sok helyet.', 'szosztalka.svg', 1900, 0),
    (3, 'Hagymaváza', 'hagymavaza', 'Kerek hasú, keskeny nyakú váza, amelyben egy-két szál virág vagy egy száraz ág is jól mutat. Belül vízzáró mázat kap.', 'hagymavaza.svg', 8900, 1),
    (3, 'Karcsú nyakú váza', 'karcsu-nyaku-vaza', 'Magas, karcsú váza hosszú szárú virágokhoz, pampafűhöz és ágakhoz. Nehéz talpú, nem borul fel.', 'karcsu-nyaku-vaza.svg', 7600, 0),
    (3, 'Bimbóváza', 'bimbovaza', 'Kis méretű váza egyetlen virágnak vagy egy apró csokornak. Ablakpárkányra, éjjeliszekrényre való.', 'bimbovaza.svg', 3600, 0),
    (4, 'Gyertyatartó', 'gyertyatarto', 'Szálgyertyához való gyertyatartó széles, mély tányérral, amely felfogja a lecsorgó viaszt. A gyertya nem tartozék.', 'gyertyatarto.svg', 3300, 0),
    (4, 'Kaspó alátéttel', 'kaspo-alatettel', 'Lefolyólyukas kaspó a hozzá tartozó alátéttel, így az öntözővíz nem a polcra folyik. Fűszernövényeknek és pozsgásoknak.', 'kaspo-alatettel.svg', 5400, 0),
    (4, 'Vajtartó fedővel', 'vajtarto-fedovel', 'A harang alakú fedél védi a vajat a portól és a szagoktól, így szobahőmérsékleten is megmarad puhának. A fedél gombja könnyen megfogható.', 'vajtarto-fedovel.svg', 6200, 0),
    (5, 'Reggeli ajándékcsomag', 'reggeli-ajandekcsomag', 'Egy Mecsek bögre (350 ml) és egy 22 cm-es lapostányér a választott színben, kraft dobozban, papírfonallal átkötve. Kész ajándék, nem kell becsomagolni.', 'reggeli-ajandekcsomag.svg', 11900, 1),
    (5, 'Teás ajándékcsomag', 'teas-ajandekcsomag', 'Egy 600 ml-es fedeles teáskanna kerámia szűrőbetéttel és két fül nélküli teáscsésze, díszdobozban.', 'teas-ajandekcsomag.svg', 16500, 1);

INSERT INTO product_variants (product_id, color, color_hex, size, price_diff, stock) VALUES
    (1, 'Homok', '#d8c1a0', '250 ml', 0, 8),
    (1, 'Homok', '#d8c1a0', '350 ml', 600, 5),
    (1, 'Mohazöld', '#7f8f69', '250 ml', 0, 6),
    (1, 'Mohazöld', '#7f8f69', '350 ml', 600, 4),
    (1, 'Éjkék', '#2f4a63', '250 ml', 0, 0),
    (1, 'Éjkék', '#2f4a63', '350 ml', 600, 3),
    (2, 'Natúr', '#eee5d6', NULL, 0, 10),
    (2, 'Szén', '#3d3a38', NULL, 0, 7),
    (3, 'Homok', '#d8c1a0', '200 ml', 0, 9),
    (3, 'Homok', '#d8c1a0', '300 ml', 400, 6),
    (3, 'Terrakotta', '#c0643d', '200 ml', 0, 5),
    (3, 'Terrakotta', '#c0643d', '300 ml', 400, 4),
    (4, 'Homok', '#d8c1a0', '22 cm', 0, 12),
    (4, 'Homok', '#d8c1a0', '27 cm', 1400, 9),
    (4, 'Mohazöld', '#7f8f69', '22 cm', 0, 7),
    (4, 'Mohazöld', '#7f8f69', '27 cm', 1400, 6),
    (4, 'Éjkék', '#2f4a63', '22 cm', 0, 8),
    (4, 'Éjkék', '#2f4a63', '27 cm', 1400, 5),
    (5, 'Homok', '#d8c1a0', '20 cm', 0, 11),
    (5, 'Éjkék', '#2f4a63', '20 cm', 0, 6),
    (6, 'Natúr', '#eee5d6', '28 cm', 0, 4),
    (6, 'Natúr', '#eee5d6', '34 cm', 3000, 3),
    (6, 'Mohazöld', '#7f8f69', '28 cm', 0, 3),
    (6, 'Mohazöld', '#7f8f69', '34 cm', 3000, 2),
    (7, 'Homok', '#d8c1a0', NULL, 0, 20),
    (7, 'Terrakotta', '#c0643d', NULL, 0, 14),
    (8, 'Homok', '#d8c1a0', 'Kicsi, 18 cm', 0, 5),
    (8, 'Homok', '#d8c1a0', 'Közepes, 24 cm', 3500, 3),
    (8, 'Homok', '#d8c1a0', 'Nagy, 32 cm', 7000, 2),
    (8, 'Terrakotta', '#c0643d', 'Kicsi, 18 cm', 0, 4),
    (8, 'Terrakotta', '#c0643d', 'Közepes, 24 cm', 3500, 3),
    (8, 'Terrakotta', '#c0643d', 'Nagy, 32 cm', 7000, 0),
    (9, 'Éjkék', '#2f4a63', NULL, 0, 4),
    (9, 'Natúr', '#eee5d6', NULL, 0, 6),
    (10, 'Homok', '#d8c1a0', NULL, 0, 9),
    (10, 'Mohazöld', '#7f8f69', NULL, 0, 6),
    (10, 'Terrakotta', '#c0643d', NULL, 0, 2),
    (11, 'Natúr', '#eee5d6', NULL, 0, 12),
    (11, 'Szén', '#3d3a38', NULL, 0, 8),
    (12, 'Homok', '#d8c1a0', '12 cm', 0, 7),
    (12, 'Homok', '#d8c1a0', '16 cm', 1800, 5),
    (12, 'Szén', '#3d3a38', '12 cm', 0, 6),
    (12, 'Szén', '#3d3a38', '16 cm', 1800, 4),
    (13, 'Natúr', '#eee5d6', NULL, 0, 5),
    (13, 'Mohazöld', '#7f8f69', NULL, 0, 4),
    (14, 'Homok', '#d8c1a0', NULL, 0, 6),
    (14, 'Mohazöld', '#7f8f69', NULL, 0, 4),
    (14, 'Éjkék', '#2f4a63', NULL, 0, 5),
    (15, 'Natúr', '#eee5d6', NULL, 0, 3),
    (15, 'Éjkék', '#2f4a63', NULL, 0, 2);

INSERT INTO shipping_methods (name, description, price, free_from, requires_address) VALUES
    ('Futárszolgálat', 'Munkanapokon, 2-3 napon belül. 20 000 Ft felett ingyenes.', 1490, 20000, 1),
    ('Expressz futár', 'A rendelést követő munkanapon.', 2490, NULL, 1),
    ('Személyes átvétel', 'Pécs, Tímár utca 11. Nyitvatartási időben.', 0, NULL, 0);

INSERT INTO orders (customer_name, phone, zip, city, street, note, shipping_method, shipping_cost, subtotal, total, status, created_at) VALUES
    ('Kovács Anna', '+36 30 555 0142', '7621', 'Pécs', 'Rákóczi út 18.', NULL, 'Futárszolgálat', 1490, 18600, 20090, 'shipped', '2026-09-21 10:14:00'),
    ('Szabó Dávid', '+36 20 555 0187', '7400', 'Kaposvár', 'Fő utca 42.', 'Szülinapi ajándék, kérem, ne legyen benne számla a dobozban.', 'Futárszolgálat', 1490, 11900, 13390, 'shipped', '2026-09-26 16:40:00'),
    ('Tóth Eszter', '+36 70 555 0113', '7630', 'Pécs', 'Siklósi út 7. 2/4.', NULL, 'Futárszolgálat', 1490, 19600, 21090, 'processing', '2026-10-01 09:05:00'),
    ('Horváth Gábor', '+36 30 555 0165', NULL, NULL, NULL, 'Szombaton délelőtt tudom átvenni.', 'Személyes átvétel', 0, 16500, 16500, 'new', '2026-10-02 18:22:00'),
    ('Nagy Réka', '+36 20 555 0129', '7633', 'Pécs', 'Mecsekoldal utca 3.', 'Ha nem vagyok otthon, kérem, csöngessenek a szomszédhoz.', 'Expressz futár', 2490, 18500, 20990, 'new', '2026-10-03 21:08:00');

INSERT INTO order_items (order_id, variant_id, product_name, variant_label, unit_price, quantity) VALUES
    (1, 2, 'Mecsek bögre', 'Homok / 350 ml', 4500, 2),
    (1, 13, 'Korong lapostányér', 'Homok / 22 cm', 4800, 2),
    (2, 47, 'Reggeli ajándékcsomag', 'Mohazöld', 11900, 1),
    (3, 31, 'Hagymaváza', 'Terrakotta / Közepes, 24 cm', 12400, 1),
    (3, 35, 'Bimbóváza', 'Homok', 3600, 2),
    (4, 50, 'Teás ajándékcsomag', 'Éjkék', 16500, 1),
    (5, 22, 'Nagy tálalótál', 'Natúr / 34 cm', 12800, 1),
    (5, 26, 'Szósztálka', 'Terrakotta', 1900, 3);
