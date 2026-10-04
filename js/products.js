// A rendezés választása azonnal frissíti a listát
document.querySelectorAll('[data-sort-form]').forEach((form) => {
    form.querySelector('select').addEventListener('change', () => form.submit());
});
