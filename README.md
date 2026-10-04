# Tűzkorong Kerámiaműhely - demó weboldal

Élő demó: https://tuzkorong-webshop-demo.kesug.com/

Készítette: Illés Gergely (fejlesztői néven: chill), webfejlesztő, Pécs.

## Mi ez, és miért készült?

A Tűzkorong Kerámiaműhely kitalált pécsi cég, az oldalon látható termékek, árak, rendelések és az admin felület adatai mintaadatok. Az oldal portfólió-bemutató munka: azt mutatja meg, hogyan néz ki egy kézműves műhely saját webshopja.

Egy kisműhelynek, amely most még közösségi oldalon vagy személyesen ad el, az oldal feladata, hogy a termékek egy helyen, árral és készlettel megtekinthetők legyenek, a vásárló bármikor leadhassa a rendelését, a műhely pedig egy felületen lássa és kezelje a beérkező rendeléseket és a kínálatot.

## Mit tud az oldal?

- A termékek kategóriák szerint szűrhetők, és rendezhetők kiemelt sorrend, ár vagy név szerint.
- A termékoldalon a vásárló színt és méretet választ. Ilyenkor az ár és a készlet is változik, a termék illusztrációja pedig felveszi a kiválasztott máz színét.
- A kosár oldalfrissítés után is megmarad, a mennyiség módosítható, a tételek törölhetők, és az oldal jelzi, mennyi hiányzik az ingyenes szállításhoz.
- A pénztárban név, telefonszám, cím, szállítási mód és megjegyzés megadása után a rendelés rögzítésre kerül. Online fizetés és e-mail nincs, a vevő az oldalon kap visszaigazolást a rendelési számmal.
- A futárszolgálat 20 000 Ft felett ingyenes, személyes átvételnél nincs szállítási díj, és nem kell címet megadni.
- A hibásan kitöltött mezők mellett érthető magyar hibaüzenet jelenik meg, a beírt adatok nem vesznek el.
- Az elfogyott változatok nem rendelhetők, a készlet rendelés után automatikusan csökken.
- A műhely az admin felületen belépés után látja az áttekintést (rendelések, fogyó készlet), a rendeléseket részletekkel, és átállíthatja az állapotukat (új, feldolgozás alatt, kiszállítva).
- Az admin felületen termékek vihetők fel, szerkeszthetők és törölhetők változatokkal együtt, a korábbi rendelések törlés után is megmaradnak.

## Szakmai összefoglaló

**Front-end**
- Szemantikus HTML, mobile first elrendezés, Bootstrap 5 és saját CSS (egyedi színváltozókkal), külön fájlokban, inline stílus és eseménykezelő nélkül.
- Vanília JavaScript, keretrendszer nélkül: a kosár `localStorage`-ban él és eseményekkel frissíti a számlálót, a termékoldal a változatválasztást és az árfrissítést kezeli, a kosár és a pénztár tételei a szerverről kért adatokból épülnek fel.
- Saját készítésű SVG termékképek `--glaze` CSS változóval; az `img.php` a kért színnel szolgálja ki őket, így a változat színe minden nézetben (termékoldal, kosár, pénztár) megjelenik. A fájlnevet és a színkódot szigorú mintával ellenőrzi.
- Háttér és díszítés (agyagpöttyözés, hullámos szekciószélek, mázfoltok, gyűrűk) SVG-ből és CSS-ből, külső kép nélkül.

**Back-end**
- PHP 8 és MySQL/MariaDB, PDO-val, natív prepared statementekkel, utf8mb4 kódolással.
- Normalizált séma külső kulcsokkal: kategóriák, termékek, változatok, szállítási módok, rendelések, rendelési tételek, adminok. A rendelési tételek a megrendelés pillanatában rögzített nevet és árat őrzik, a termék törlése nem érinti őket.
- A böngészőből csak a változat azonosítója és a mennyiség érkezik, az árat, a szállítási díjat és a készletet a szerver számolja újra az adatbázisból.
- A rendelés tranzakcióban fut, a készletet `SELECT ... FOR UPDATE` zárolással ellenőrzi és vonja le, így egyszerre érkező rendeléseknél sem fogy el több darab, mint amennyi van.
- A kéréseket egy közös segédfüggvény alakítja szöveggé vagy egész számmá, így a tömbként küldött vagy hibás paraméterek sem okoznak hibát. A kosár API csak POST kérést fogad, hibás JSON-ra 400-at, más módszerre 405-öt ad magyar hibaüzenettel.
- Admin belépés `password_hash` / `password_verify`-val, munkamenet-azonosító cseréjével belépéskor és lassítással sikertelen próbálkozásnál. A munkamenet-süti `HttpOnly` és `SameSite=Lax`, HTTPS-en `Secure`.
- A hibanaplóba az adatbázis-kapcsolati hibáknál csak a hibakód kerül, hostnév, felhasználónév és jelszó nem. A hibák a felületen nem jelennek meg.

**Minőség**
- Biztonság: prepared statementek (SQL injection ellen), kimenet-escape-elés (XSS ellen), CSRF token minden űrlapon, szerveroldali validáció, robotcsapda mező a pénztárban, a konfiguráció, az `includes/` és az `sql/` mappa közvetlen elérésének tiltása `.htaccess`-szel, a konfigurációs fájl verziókezelésen kívül.
- SEO: oldalankénti `title` és `meta description`, Open Graph címkék, `lang="hu"`, termékoldalon JSON-LD strukturált adat, tiszta címsorszerkezet oldalanként egy `h1`-gyel.
- Akadálymentesség: "Ugrás a tartalomra" hivatkozás, minden mezőhöz címke, ARIA attribútumok a változatválasztón és a tooltipen, billentyűzettel is elérhető vezérlők, látható fókuszjelölés, `prefers-reduced-motion` figyelembevétele, minden képnek `alt` attribútuma.

**Tesztelés**
- Szintaxis: `php -l` mind a 29 PHP fájlra, `node --check` mind a 6 JavaScript fájlra. Statikus keresés maradékokra (TODO, `console.*`, `var_dump`, emoji, inline stílus), használatlan CSS-osztályokra, változókra, függvényekre és fájlokra.
- Böngésző: Chromiumban (Playwright) 320, 390, 768, 1280 és 1920 px szélességen minden oldal ellenőrizve: nincs vízszintes túlcsordulás, konzolhiba vagy hibás kérés, nincs duplikált azonosító, törött horgony vagy hiányzó ARIA-hivatkozás. Mintegy 140 szkriptelt ellenőrzés fedi a szűrőket, a rendezést, a változatválasztást, a kosarat, a pénztárat (üres, hibás és helyes adatokkal, személyes átvétellel) és az admin felületet (belépés, állapotváltás, termék felvitele, szerkesztése, törlése). Más böngészőben és valódi eszközön nem néztem.
- Végpontok: `curl`-lel kipróbálva a CSRF nélküli, hibás, tömbként küldött és üres kérések, az SQL injekciós és XSS próbaszövegek, a hamisított ár, a készletet meghaladó mennyiség, a robotcsapda és a védett oldalak belépés nélküli elérése. Külön próbán egyszerre leadott rendelések is megerősítették, hogy a készlet nem megy nulla alá.
- Környezet: MariaDB 10.11 szigorú módban (tiszta importtal), PHP 8.3, valamint Apache 2.4 `mod_php`-val és `.htaccess`-szel, ahol az `includes/` és az `sql/` mappa 403-at ad.

## Felhasználás

Az oldal bemutató célú, a kód és a tartalom a készítő munkája, a cég és az adatok kitaláltak.

## Továbbfejlesztési irányok

Az alábbi bővítések mindegyikét magam is meg tudom valósítani:

- Képfeltöltés az admin felületről méretezéssel és fájltípus-ellenőrzéssel, több kép termékenként és galéria a termékoldalon.
- E-mail értesítés a vevőnek és a műhelynek a beérkező rendelésről, saját SMTP-vel.
- Online fizetés bekapcsolása a rendelés rögzítése után.
- Rendelés lemondása készlet-visszaírással, számlázó rendszer összekötése.
- Termékkeresés, kuponkód és vevői fiók kívánságlistával.
- És még sok más: számtalan további fejlesztés elérhető, az ügyfél igényei szerint
