document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('workspace-channel');
    if (!select) return;
    if (window.jQuery && jQuery.fn.select2) {
        const picker = jQuery(select).select2({width:'100%', placeholder:select.dataset.placeholder, dir:document.documentElement.dir || 'ltr'});
        picker.on('select2:open', function () {
            const dropdown = document.querySelector('.select2-container--open .select2-dropdown');
            if (dropdown) dropdown.classList.add('ip-channel-dropdown');
            const input = dropdown && dropdown.querySelector('.select2-search__field');
            if (input) { input.setAttribute('aria-label',select.dataset.placeholder); input.focus(); }
        });
        picker.on('change', function () { if (select.value) window.location.assign(select.value); });
    } else {
        select.addEventListener('change', function () { if (select.value) window.location.assign(select.value); });
    }
});
