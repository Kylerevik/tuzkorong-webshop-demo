// Termékoldal: szín- és méretválasztás, ár és készlet frissítése, kosárba tétel.
(function () {
    const root = document.querySelector('[data-product-detail]');
    if (!root) {
        return;
    }

    const variants = JSON.parse(root.dataset.variants);
    if (variants.length === 0) {
        return;
    }

    const productName = root.dataset.productName;
    const priceElement = root.querySelector('[data-price]');
    const stockElement = root.querySelector('[data-stock-status]');
    const quantityInput = root.querySelector('[data-quantity]');
    const imageElement = root.querySelector('[data-product-image]');
    const addButton = root.querySelector('[data-add-to-cart]');
    const messageElement = root.querySelector('[data-message]');
    const colorList = root.querySelector('[data-options="color"]');
    const sizeList = root.querySelector('[data-options="size"]');

    const colors = [...new Set(variants.map((variant) => variant.color))];
    const sizes = [...new Set(variants.map((variant) => variant.size))];
    const selection = { color: null, size: null };

    function findVariant(color, size) {
        return variants.find((variant) => variant.color === color && variant.size === size);
    }

    function sizesFor(color) {
        return variants.filter((variant) => variant.color === color).map((variant) => variant.size);
    }

    function currentVariant() {
        return findVariant(selection.color, selection.size);
    }

    function stockText(stock) {
        if (stock <= 0) {
            return 'Elfogyott';
        }
        return stock <= 3 ? `Utolsó ${stock} darab` : 'Készleten';
    }

    function createOption(selected, onSelect) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'option';
        button.setAttribute('aria-pressed', String(selected));
        button.addEventListener('click', onSelect);
        return button;
    }

    function renderColors() {
        colorList.replaceChildren(...colors.map((color) => {
            const variant = variants.find((item) => item.color === color);
            const button = createOption(color === selection.color, () => selectColor(color));
            button.classList.add('option--color');
            button.title = color;
            button.setAttribute('aria-label', color);

            const swatch = document.createElement('span');
            swatch.className = 'option__swatch';
            swatch.style.backgroundColor = variant.colorHex;
            button.append(swatch);
            return button;
        }));
    }

    function renderSizes() {
        const available = sizesFor(selection.color);
        sizeList.replaceChildren(...sizes.map((size) => {
            const button = createOption(size === selection.size, () => selectSize(size));
            button.textContent = size;
            button.disabled = !available.includes(size);
            const variant = findVariant(selection.color, size);
            button.classList.toggle('is-soldout', Boolean(variant) && variant.stock <= 0);
            return button;
        }));
    }

    function selectColor(color) {
        selection.color = color;
        if (!sizesFor(color).includes(selection.size)) {
            const inStock = variants.find((variant) => variant.color === color && variant.stock > 0);
            selection.size = (inStock || findVariant(color, sizesFor(color)[0])).size;
        }
        refresh();
    }

    function selectSize(size) {
        selection.size = size;
        refresh();
    }

    function refresh() {
        const variant = currentVariant();

        if (colorList) {
            renderColors();
            root.querySelector('[data-selected="color"]').textContent = selection.color;
        }
        if (sizeList) {
            renderSizes();
            root.querySelector('[data-selected="size"]').textContent = selection.size;
        }

        if (imageElement.dataset.imageFile && variant.colorHex) {
            imageElement.src = `img.php?f=${encodeURIComponent(imageElement.dataset.imageFile)}&color=${variant.colorHex.slice(1)}`;
        }
        priceElement.textContent = Cart.formatPrice(variant.price);
        stockElement.textContent = stockText(variant.stock);
        stockElement.classList.toggle('stock-status--out', variant.stock <= 0);

        quantityInput.max = Math.max(variant.stock, 1);
        quantityInput.value = Math.min(Number(quantityInput.value) || 1, Math.max(variant.stock, 1));
        quantityInput.disabled = variant.stock <= 0;
        root.querySelectorAll('[data-quantity-step]').forEach((button) => {
            button.disabled = variant.stock <= 0;
        });
        addButton.disabled = variant.stock <= 0;
        messageElement.textContent = '';
    }

    function showMessage(text, linkText) {
        messageElement.textContent = text + ' ';
        if (linkText) {
            const link = document.createElement('a');
            link.href = 'cart.php';
            link.textContent = linkText;
            messageElement.append(link);
        }
    }

    function addToCart() {
        const variant = currentVariant();
        const wanted = Math.max(1, Math.floor(Number(quantityInput.value) || 1));
        const result = Cart.add(variant.id, wanted, variant.stock);
        const label = [variant.color, variant.size].filter(Boolean).join(' / ');

        if (result.added === 0) {
            showMessage(`Ebből a változatból már a teljes készlet (${result.inCart} db) a kosarában van.`, 'Kosár megtekintése');
        } else if (result.added < wanted) {
            showMessage(`Csak ${result.added} darabot tudtunk betenni, mert több nincs készleten.`, 'Kosár megtekintése');
        } else {
            showMessage(`A kosárba került: ${productName}${label ? ' (' + label + ')' : ''}, ${result.added} db.`, 'Kosár megtekintése');
        }
    }

    root.querySelectorAll('[data-quantity-step]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = Number(quantityInput.value) + Number(button.dataset.quantityStep);
            quantityInput.value = Math.min(Math.max(next, 1), Number(quantityInput.max));
        });
    });
    quantityInput.addEventListener('change', () => {
        const value = Math.floor(Number(quantityInput.value)) || 1;
        quantityInput.value = Math.min(Math.max(value, 1), Number(quantityInput.max));
    });
    addButton.addEventListener('click', addToCart);

    const first = variants.find((variant) => variant.stock > 0) || variants[0];
    selection.color = first.color;
    selection.size = first.size;
    refresh();
})();
