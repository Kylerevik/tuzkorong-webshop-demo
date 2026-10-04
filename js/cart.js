// Kosár: a tartalom a localStorage-ban él, az árakat és a készletet a szerver adja (api/cart-lines.php).
(function () {
    const STORAGE_KEY = 'tuzkorong_cart';
    const MAX_QUANTITY = 99;

    function load() {
        try {
            const stored = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (!Array.isArray(stored)) {
                return [];
            }
            return stored.filter((item) => Number.isInteger(item.variantId) && Number.isInteger(item.quantity) && item.quantity > 0);
        } catch (error) {
            return [];
        }
    }

    let items = load();

    function save() {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
        } catch (error) {
            // Tiltott tárhelynél (pl. privát mód) a kosár az oldal bezárásáig megmarad
        }
        document.dispatchEvent(new CustomEvent('cart:change'));
    }

    function count() {
        return items.reduce((sum, item) => sum + item.quantity, 0);
    }

    function find(variantId) {
        return items.find((item) => item.variantId === variantId);
    }

    function add(variantId, quantity, stock) {
        const existing = find(variantId);
        const inCart = existing ? existing.quantity : 0;
        const added = Math.min(quantity, stock - inCart, MAX_QUANTITY - inCart);

        if (added <= 0) {
            return { added: 0, inCart };
        }
        if (existing) {
            existing.quantity += added;
        } else {
            items.push({ variantId, quantity: added });
        }
        save();
        return { added, inCart: inCart + added };
    }

    function setQuantity(variantId, quantity) {
        const item = find(variantId);
        if (!item) {
            return;
        }
        if (quantity < 1) {
            remove(variantId);
            return;
        }
        item.quantity = Math.min(quantity, MAX_QUANTITY);
        save();
    }

    function remove(variantId) {
        items = items.filter((item) => item.variantId !== variantId);
        save();
    }

    function clear() {
        items = [];
        save();
    }

    function formatPrice(amount) {
        return String(amount).replace(/\B(?=(\d{3})+(?!\d))/g, '\u00A0') + '\u00A0Ft';
    }

    async function fetchLines() {
        if (items.length === 0) {
            return { lines: [], adjusted: false };
        }

        const response = await fetch('api/cart-lines.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(items),
        });
        if (!response.ok) {
            throw new Error('A kosár adatai most nem érhetők el.');
        }

        const data = await response.json();
        const byId = new Map(data.lines.map((line) => [line.variantId, line]));
        const lines = [];
        const kept = [];
        let adjusted = false;

        items.forEach((item) => {
            const line = byId.get(item.variantId);
            if (!line || !line.available) {
                adjusted = true;
                return;
            }
            const quantity = Math.min(item.quantity, line.stock);
            adjusted = adjusted || quantity !== item.quantity;
            kept.push({ variantId: item.variantId, quantity });
            lines.push({ ...line, quantity });
        });

        items = kept;
        if (adjusted) {
            save();
        }
        return { lines, adjusted };
    }

    function updateBadge() {
        const total = count();
        document.querySelectorAll('[data-cart-count]').forEach((badge) => {
            badge.textContent = total;
            badge.hidden = total === 0;
        });
    }

    document.addEventListener('cart:change', updateBadge);
    window.addEventListener('storage', (event) => {
        if (event.key === STORAGE_KEY) {
            items = load();
            document.dispatchEvent(new CustomEvent('cart:change'));
        }
    });

    window.Cart = {
        items: () => items.map((item) => ({ ...item })),
        count,
        add,
        setQuantity,
        remove,
        clear,
        formatPrice,
        fetchLines,
    };

    // A visszaigazoló oldalon a rendelés leadása után ürítjük a kosarat
    if (document.querySelector('[data-clear-cart]')) {
        clear();
    }
    updateBadge();
})();
