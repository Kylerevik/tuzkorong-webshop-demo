# Tűzkorong Kerámiaműhely - webshop demó

Egy kitalált pécsi kerámiaműhely webshopja. Portfólió demó: működő katalógus, kosár, megrendelés rögzítése (fizetés nélkül) és admin felület.

**Stack:** HTML, CSS, Bootstrap 5 (CDN), vanilla JavaScript, PHP 8.1+ és MySQL/MariaDB (PDO).

## Funkciók

- Főoldal, termékek listája kategória szerinti szűréssel és rendezéssel (kiemelt, ár, név)
- Termékoldal szín- és méretváltozatokkal, amelyek az árat és a készletet is módosítják
- Kosár JavaScripttel (mennyiség módosítása, törlés, végösszeg), amely oldalfrissítés után is megmarad
- Pénztár: név, telefonszám, cím, szállítási mód, megjegyzés. Fizetés és e-mail nincs, a rendelés az adatbázisba kerül, az oldalon visszaigazolást kap a vevő
- Szerveroldali ellenőrzés: az árakat, a szállítási díjat és a készletet a szerver az adatbázisból számolja újra, a böngészőből csak a változat azonosítója és a mennyiség érkezik. A készletlevonás tranzakcióban, zárolással történik
- Admin felület belépéssel: termékek felvitele, szerkesztése, törlése (változatokkal), rendelések listája, részletei, állapotváltás (új, feldolgozás alatt, kiszállítva)
- Védelem: prepared statementek, kimenet-escape-elés (XSS), CSRF token minden űrlapon, jelszó `password_hash`-sel tárolva

## Admin demó belépés

Cím: `/admin/login.php`

| Felhasználónév | Jelszó |
|---|---|
| `admin` | `Agyag2026` |

A belépő oldalon is látszik. Éles használat előtt cseréld le (lásd lent).

## Fájlok

```
tuzkorong/
├── index.php              főoldal
├── products.php           terméklista (szűrés, rendezés)
├── product.php            termékoldal változatokkal
├── cart.php               kosár
├── checkout.php           pénztár, rendelés rögzítése
├── confirmation.php       visszaigazolás
├── img.php                SVG termékkép kiszolgálása a kiválasztott szín máz színével
├── api/
│   └── cart-lines.php     a kosár tételeinek aktuális ára és készlete (JSON)
├── admin/
│   ├── login.php, logout.php
│   ├── index.php          áttekintés
│   ├── orders.php         rendelések listája
│   ├── order.php          rendelés részletei, állapotváltás
│   ├── products.php       termékek listája, törlés
│   └── product-edit.php   termék felvitele és szerkesztése
├── includes/
│   ├── config.php         adatbázis-adatok (a config.example.php másolata, nincs verziókezelésben)
│   ├── config.example.php minta a config.php-hoz
│   ├── bootstrap.php      munkamenet, betöltések
│   ├── db.php             PDO kapcsolat
│   ├── functions.php      közös segédfüggvények
│   ├── catalog.php        termék- és kategória-lekérdezések
│   ├── orders.php         kosár ellenőrzése, rendelés rögzítése
│   ├── auth.php           admin belépés
│   ├── product-admin.php  admin termékkezelés (validálás, mentés)
│   ├── header.php, footer.php, product-card.php
│   ├── admin-header.php, admin-footer.php
│   └── .htaccess          közvetlen elérés tiltása
├── css/  style.css, admin.css
├── js/   cart.js, product.js, products.js, cart-page.js, checkout.js, admin.js
├── img/
│   ├── logo.svg, hero.svg
│   └── products/          termékképek
├── sql/
│   ├── database.sql       táblák és mintaadatok (15 termék, 50 változat, 5 rendelés)
│   └── .htaccess          közvetlen elérés tiltása
├── .gitignore
└── README.md
```

## Futtatás helyben (XAMPP)

1. Másold a `tuzkorong` mappát a `C:\xampp\htdocs\` alá.
2. Az XAMPP Control Panelben indítsd el az Apache-ot és a MySQL-t.
3. A phpMyAdminban (`http://localhost/phpmyadmin`) hozz létre egy `tuzkorong` nevű adatbázist `utf8mb4_unicode_ci` karakterkészlettel.
4. Jelöld ki az adatbázist, és az Importálás fülön töltsd fel az `sql/database.sql` fájlt.
5. Másold le az `includes/config.example.php` fájlt `includes/config.php` néven, és írd át az XAMPP adataira: host `localhost`, adatbázis `tuzkorong`, felhasználó `root`, jelszó üres (`''`).
6. Nyisd meg: `http://localhost/tuzkorong/`

PHP 8.1 vagy újabb kell.

## Telepítés InfinityFree-re

1. A vezérlőpulton (Control Panel) a **MySQL Databases** oldalon hozz létre egy adatbázist. Jegyezd fel a teljes nevét (`if0_...` előtaggal), a felhasználónevet, a jelszót és a MySQL hostnevet (`sqlXXX.infinityfree.com`).
2. Az adatbázis mellett a **phpMyAdmin** gombbal nyisd meg a kezelőt, jelöld ki az adatbázist, és az Importálás fülön töltsd fel az `sql/database.sql` fájlt.
3. Másold le az `includes/config.example.php` fájlt `includes/config.php` néven, és írd bele az 1. pontban feljegyzett adatokat.
4. Töltsd fel a projekt tartalmát a `htdocs` mappába FTP-vel (pl. FileZilla) vagy az Online File Managerrel. A mappaszerkezet maradjon meg. Az `sql` mappát nem kötelező feltölteni.
5. Az InfinityFree-n a PHP-verzió a vezérlőpulton állítható, a 8.1 és a 8.4 egyaránt jó.
6. Nyisd meg a domainedet, az admin a `/admin/login.php` címen érhető el. Az ingyenes SSL tanúsítványt a vezérlőpulton kérheted, HTTPS-en a munkamenet-süti automatikusan `secure` lesz.

A tárhely korlátai (nincs `mail()`, nincs cron, nincs távoli MySQL) nem érintik az oldalt: e-mailt nem küld, időzített feladata nincs, az adatbázist csak a helyi PHP éri el.

## Képek

A termékekhez saját készítésű SVG illusztrációk tartoznak az `img/products/` mappában, így nincs idegen vagy védett kép a projektben.

Valódi fotók így kerülhetnek be:

1. Másold a fényképeket az `img/products/` mappába (ajánlott: négyzet alakú JPG vagy WebP, kb. 800x800 px, kis fájlméret).
2. Az adminban a termék szerkesztésekor a **Kép** listában megjelenik az új fájl, válaszd ki, és mentsd.

A termék SVG illusztrációi `--glaze` CSS változóval készültek, ezért a termékoldalon, a kosárban és a pénztári összegzésben a kiválasztott színnek megfelelő színben jelennek meg (`img.php?f=fajl.svg&color=7f8f69`). A valódi fotók nem színezhetők át, ott minden változatnál ugyanaz a kép látszik, amíg változatonként külön képet nem rendelsz hozzájuk.

Ha egy terméknél nincs kép megadva, a webshop a `placeholder.svg` ábrát mutatja. A `hero.svg` a főoldal nyitóképe, ezt is lecserélheted fotóra.

## Admin jelszó cseréje

Új jelszó hasheléséhez futtasd le (parancssorban):

```
php -r "echo password_hash('az-uj-jelszo', PASSWORD_BCRYPT), PHP_EOL;"
```

Majd phpMyAdminban frissítsd az `admins` tábla `password_hash` oszlopát a kapott értékkel.

## Hogyan fejleszthető tovább

- Képfeltöltés az adminból (méretezéssel és fájltípus-ellenőrzéssel)
- Több kép termékenként, képgaléria a termékoldalon
- E-mail értesítés a vevőnek és a műhelynek (saját SMTP-vel, mert az InfinityFree nem engedi a `mail()` függvényt)
- Online fizetés (pl. bankkártyás szolgáltató bekapcsolása a rendelés rögzítése után)
- Rendelés lemondása készlet-visszaírással, számlázó rendszer összekötése
- Termékkeresés, kuponkód, vevői fiók, kívánságlista
- Több admin felhasználó, jelszócsere az admin felületen, belépési kísérletek korlátozása
- Adatkezelési tájékoztató és süti-nyilatkozat az éles indulás előtt
- Automata tesztek a rendelés- és készletlogikához
