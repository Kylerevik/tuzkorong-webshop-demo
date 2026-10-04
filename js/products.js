document.querySelectorAll('[data-sort-form]').forEach((form) => {
    form.querySelector('select').addEventListener('change', () => form.submit());
});
