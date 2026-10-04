// Pénztár: kosár összegzése, szállítási díj számolása, cím mezők kezelése.
(function () {
    const form = document.querySelector('[data-checkout-form]');
    const loading = document.querySelector('[data-checkout-loading]');
    const notice = document.querySelector('[data-checkout-notice]');
    const emptyBox = document.querySelector('[data-checkout-empty]');
    const summaryList = form.querySelector('[data-summary-list]');
    const subtotalElement = form.querySelector('[data-summary-subtotal]');
    const shippingElement = form.querySelector('[data-summary-shipping]');
    const totalElement = form.querySelector('[data-summary-total]');
    const cartField = form.querySelector('[data-cart-field]');
    const addressFields = form.querySelector('[data-address-fields]');
    const addressInputs = form.querySelectorAll('[data-address-input]');
    const template = document.getElementById('summary-line-template');

    let subtotal = 0;

    function buildLine(line) {
        const node = template.content.firstElementChild.cloneNode(true);
        node.querySelector('[data-line-image]').src = line.image;
        node.querySelector('[data-line-name]').textContent = `${line.productName} × ${line.quantity}`;
        node.querySelector('[data-line-label]').textContent = line.label;
        node.querySelector('[data-line-total]').textContent = Cart.formatPrice(line.price * line.quantity);
        return node;
    }

    function selectedMethod() {
        return form.querySelector('input[name="shipping_method_id"]:checked');
    }

    function shippingCost(method) {
        const freeFrom = Number(method.dataset.freeFrom) || null;
        return freeFrom !== null && subtotal >= freeFrom ? 0 : Number(method.dataset.price);
    }

    function refreshTotals() {
        const method = selectedMethod();
        const shipping = method ? shippingCost(method) : 0;

        subtotalElement.textContent = Cart.formatPrice(subtotal);
        shippingElement.textContent = shipping === 0 ? 'Ingyenes' : Cart.formatPrice(shipping);
        totalElement.textContent = Cart.formatPrice(subtotal + shipping);
    }

    function refreshAddressFields() {
        const method = selectedMethod();
        const required = Boolean(method) && method.dataset.requiresAddress === '1';

        addressFields.hidden = !required;
        addressInputs.forEach((input) => {
            input.required = required;
        });
    }

    form.querySelectorAll('input[name="shipping_method_id"]').forEach((radio) => {
        radio.addEventListener('change', () => {
            refreshTotals();
            refreshAddressFields();
        });
    });

    form.addEventListener('submit', () => {
        cartField.value = JSON.stringify(Cart.items());
        form.querySelector('[type="submit"]').disabled = true;
    });

    Cart.fetchLines()
        .then((result) => {
            if (result.lines.length === 0) {
                emptyBox.hidden = false;
                return;
            }
            if (result.adjusted) {
                notice.textContent = 'Néhány tétel készlete megváltozott vagy már nem kapható, ezért frissítettük a kosarát.';
                notice.hidden = false;
            }
            subtotal = result.lines.reduce((sum, line) => sum + line.price * line.quantity, 0);
            summaryList.replaceChildren(...result.lines.map(buildLine));
            refreshTotals();
            refreshAddressFields();
            form.hidden = false;
        })
        .catch((error) => {
            notice.textContent = error.message;
            notice.hidden = false;
        })
        .finally(() => {
            loading.hidden = true;
        });
})();
