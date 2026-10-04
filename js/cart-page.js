// Kosár oldal: tételek listája, mennyiség módosítása, törlés, részösszeg.
(function () {
    const loading = document.querySelector('[data-cart-loading]');
    const notice = document.querySelector('[data-cart-notice]');
    const emptyBox = document.querySelector('[data-cart-empty]');
    const content = document.querySelector('[data-cart-content]');
    const list = document.querySelector('[data-cart-list]');
    const subtotalElement = document.querySelector('[data-cart-subtotal]');
    const hintElement = document.querySelector('[data-cart-hint]');
    const template = document.getElementById('cart-line-template');
    const freeShippingFrom = Number(content.dataset.freeShippingFrom) || null;

    let lines = [];

    function buildLine(line) {
        const node = template.content.firstElementChild.cloneNode(true);
        node.dataset.variantId = line.variantId;

        const imageLink = node.querySelector('[data-line-image-link]');
        imageLink.href = line.url;
        imageLink.tabIndex = -1;
        node.querySelector('[data-line-image]').src = line.image;

        const name = node.querySelector('[data-line-name]');
        name.href = line.url;
        name.textContent = line.productName;

        node.querySelector('[data-line-label]').textContent = line.label;
        node.querySelector('[data-line-unit]').textContent = Cart.formatPrice(line.price) + ' / db';
        node.querySelector('[data-line-quantity]').max = line.stock;
        refreshLine(node, line);
        return node;
    }

    function refreshLine(node, line) {
        node.querySelector('[data-line-quantity]').value = line.quantity;
        node.querySelector('[data-line-total]').textContent = Cart.formatPrice(line.price * line.quantity);
    }

    function refreshSummary() {
        const subtotal = lines.reduce((sum, line) => sum + line.price * line.quantity, 0);
        subtotalElement.textContent = Cart.formatPrice(subtotal);

        if (freeShippingFrom === null) {
            hintElement.textContent = '';
        } else if (subtotal >= freeShippingFrom) {
            hintElement.textContent = 'A futárszolgálattal történő szállítás ingyenes.';
        } else {
            hintElement.textContent = `Még ${Cart.formatPrice(freeShippingFrom - subtotal)} hiányzik az ingyenes szállításhoz.`;
        }

        emptyBox.hidden = lines.length > 0;
        content.hidden = lines.length === 0;
    }

    function changeQuantity(node, quantity) {
        const line = lines.find((item) => item.variantId === Number(node.dataset.variantId));
        line.quantity = Math.min(Math.max(Math.floor(quantity) || 1, 1), line.stock);
        Cart.setQuantity(line.variantId, line.quantity);
        refreshLine(node, line);
        refreshSummary();
    }

    function removeLine(node) {
        const variantId = Number(node.dataset.variantId);
        lines = lines.filter((line) => line.variantId !== variantId);
        Cart.remove(variantId);
        node.remove();
        refreshSummary();
    }

    list.addEventListener('click', (event) => {
        const node = event.target.closest('[data-variant-id]');
        if (!node) {
            return;
        }
        const step = event.target.closest('[data-line-step]');
        if (step) {
            const input = node.querySelector('[data-line-quantity]');
            changeQuantity(node, Number(input.value) + Number(step.dataset.lineStep));
        } else if (event.target.closest('[data-line-remove]')) {
            removeLine(node);
        }
    });

    list.addEventListener('change', (event) => {
        const input = event.target.closest('[data-line-quantity]');
        if (input) {
            changeQuantity(input.closest('[data-variant-id]'), Number(input.value));
        }
    });

    Cart.fetchLines()
        .then((result) => {
            lines = result.lines;
            list.replaceChildren(...lines.map(buildLine));
            if (result.adjusted) {
                notice.textContent = 'Néhány tétel készlete megváltozott vagy már nem kapható, ezért frissítettük a kosarát.';
                notice.hidden = false;
            }
            refreshSummary();
        })
        .catch((error) => {
            notice.textContent = error.message;
            notice.hidden = false;
        })
        .finally(() => {
            loading.hidden = true;
        });
})();
