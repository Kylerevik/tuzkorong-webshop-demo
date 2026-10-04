// Admin: törlés megerősítése és a termék változat sorainak kezelése.
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

(function () {
    const rows = document.querySelector('[data-variant-rows]');
    const template = document.getElementById('variant-row-template');
    const addButton = document.querySelector('[data-add-variant]');
    if (!rows || !template || !addButton) {
        return;
    }

    // Az új sorok indexe nem ütközhet a meglévőkével, törölt sorok után sem
    const usedIndexes = [...rows.querySelectorAll('input[name$="[id]"]')]
        .map((input) => Number(input.name.match(/variants\[(\d+)\]/)[1]));
    let nextIndex = Math.max(-1, ...usedIndexes) + 1;

    addButton.addEventListener('click', () => {
        rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex)));
        nextIndex += 1;
    });

    rows.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-variant]');
        if (removeButton) {
            removeButton.closest('[data-variant-row]').remove();
        }
    });
})();
