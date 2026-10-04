# Tűzkorong Kerámiaműhely - demó weboldal

Élő demó: https://tuzkorong-webshop-demo.kesug.com/

Készítette: Illés Gergely (fejlesztői néven: chill), webfejlesztő, Pécs.

## Mi ez, és miért készült?

A Tűzkorong Kerámiaműhely kitalált pécsi cég, az oldalon látható termékek, árak, rendelések és az admin felület adatai mintaadatok. Az oldal portfólió-bemutató munka: azt mutatja meg, hogyan néz ki egy kézműves műhely saját webshopja.

Egy kisműhelynek, amely most még közösségi oldalon vagy személyesen ad el, az oldal feladata, hogy a termékek egy helyen, árral és készlettel megtekinthetők legyenek, a vásárló bármikor leadhassa a rendelését, a műhely pedig egy felületen lássa és kezelje a beérkező rendeléseket és a kínálatot.

## Mit tud az oldal?

- A termékek kategóriák szerint szűrhetők, és rendezhetők kiemelt sorrend, ár vagy név szerint.
- A termékoldalon a vásárló színt és méretet választ. Ilyenkor az ár és a készlet is változik, a termék illusztrációja pedig felveszi a kiválasztott máz színét.
- A kosár oldalfrissítés után is megmarad, a mennyiség módosítható, a tételek törölhetők.
- A pénztárban név, telefonszám, cím, szállítási mód és megjegyzés megadása után a rendelés rögzítésre kerül. Online fizetés és e-mail nincs, a vevő az oldalon kap visszaigazolást a rendelési számmal.
- A futárszolgálat 20 000 Ft felett ingyenes, személyes átvételnél nincs szállítási díj.
- Az elfogyott változatok nem rendelhetők, a készlet rendelés után automatikusan csökken.
- A műhely az admin felületen belépés után látja az áttekintést (rendelések, alacsony készlet), a rendeléseket részletekkel, és átállíthatja az állapotukat (új, feldolgozás alatt, kiszállítva).
- Az admin felületen termékek vihetők fel, szerkeszthetők és törölhetők változatokkal együtt, a korábbi rendelések törlés után is megmaradnak.

## Szakmai összefoglaló

**Front-end**
- Szemantikus HTML, mobile first elrendezés, Bootstrap 5 és saját CSS (egyedi színváltozókkal), külön fájlokban.
- Vanília JavaScript, keretrendszer nélkül: a kosár `localStorage`-ban él és eseményekkel frissíti a számlálót, a termékoldal a változatválasztást és az árfrissítést kezeli, a kosár és a pénztár tételei a szerverről kért adatokból épülnek fel.
- Saját készítésű SVG termékképek `--glaze` CSS változóval; az `img.php` a kért színnel szolgálja ki őket, így a változat színe minden nézetben (termékoldal, kosár, pénztár) megjelenik.

**Back-end**
- PHP 8 és MySQL/MariaDB, PDO-val, natív prepared statementekkel, utf8mb4 kódolással.
- Normalizált séma külső kulcsokkal: kategóriák, termékek, változatok, szállítási módok, rendelések, rendelési tételek, adminok. A rendelési tételek a megrendelés pillanatában rögzített nevet és árat őrzik.
- A böngészőből csak a változat azonosítója és a mennyiség érkezik, az árat, a szállítási díjat és a készletet a szerver számolja újra az adatbázisból.
- A rendelés tranzakcióban fut, a készletet `SELECT ... FOR UPDATE` zárolással ellenőrzi és vonja le, így egyszerre érkező rendeléseknél sem fogy el több darab, mint amennyi van.
- Admin belépés `password_hash` / `password_verify`-val, munkamenet-azonosító cseréjével belépéskor és lassítással sikertelen próbálkozásnál.
- A visszaigazoló oldal a munkamenetből olvassa a rendelést, így a rendelési számok nem találgathatók az URL-ből.

**Minőség**
- Biztonság: prepared statementek (SQL injection ellen), kimenet-escape-elés (XSS ellen), CSRF token minden űrlapon, szerveroldali validáció, a konfiguráció és az `includes/` mappa közvetlen elérésének tiltása.
- SEO: oldalankénti `title` és `meta description`, Open Graph címkék, `lang="hu"`, termékoldalon JSON-LD strukturált adat, tiszta, szemantikus címsorszerkezet.
- Akadálymentesség: "Ugrás a tartalomra" hivatkozás, ARIA attribútumok a változatválasztón és a tooltipen, billentyűzettel is elérhető vezérlők, `prefers-reduced-motion` figyelembevétele.

## Felhasználás

Az oldal bemutató célú, a kód és a tartalom a készítő munkája, a cég és az adatok kitaláltak.

## Továbbfejlesztési irányok

- Képfeltöltés az admin felületről, több kép termékenként, galéria a termékoldalon.
- E-mail értesítés a vevőnek és a műhelynek a beérkező rendelésről.
- Online fizetés bekapcsolása a rendelés rögzítése után.
- Rendelés lemondása készlet-visszaírással, számlázó rendszer összekötése.
- Termékkeresés, kuponkód, vevői fiók.
