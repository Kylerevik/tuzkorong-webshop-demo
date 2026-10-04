</main>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <p class="footer-brand">Tűzkorong Kerámiaműhely</p>
                <p class="mb-0">Kézzel korongozott edények Pécsről. Minden darab a műhelyben készül, kőedény agyagból, ólommentes mázzal.</p>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <h2 class="footer-title">Elérhetőség</h2>
                <address class="mb-0">
                    7621 Pécs, Tímár utca 11.<br>
                    <a href="tel:+3672555012">+36 72 555 012</a><br>
                    <a href="mailto:muhely@tuzkorong.example">muhely@tuzkorong.example</a>
                </address>
            </div>
            <div class="col-12 col-sm-6 col-md-4">
                <h2 class="footer-title">Nyitvatartás</h2>
                <p class="mb-0">Hétfőtől péntekig: 10:00 - 17:00<br>Szombat: 9:00 - 13:00<br>Vasárnap: zárva</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="mb-0">&copy; 2026 Tűzkorong Kerámiaműhely. Demó webshop, a rendelések nem teljesülnek.</p>
            <p class="mb-0">
                Készítette:
                <span class="maker"><span class="maker__name" tabindex="0" aria-describedby="maker-tip">chill</span><span class="maker__tip" id="maker-tip" role="tooltip">Illés Gergely</span></span>, webfejlesztő
                &middot; <a href="admin/login.php">Admin</a>
            </p>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/cart.js"></script>
<?php foreach ($pageScripts ?? [] as $script): ?>
<script src="<?= e($script) ?>"></script>
<?php endforeach; ?>
</body>
</html>
