/* Navigation enhancement: filters only links already permitted by the server. */
(function () {
    'use strict';
    var input = document.getElementById('workspace-nav-search');
    var menu = document.querySelector('.app-sidebar .side-menu');
    if (!input || !menu) return;
    var items = Array.from(menu.querySelectorAll('li.slide'));
    var categories = Array.from(menu.querySelectorAll('.side-item-category'));
    var empty = document.getElementById('workspace-nav-empty');
    input.addEventListener('input', function () {
        var term = input.value.trim().toLocaleLowerCase();
        var count = 0;
        items.forEach(function (item) {
            item.hidden = !item.textContent.toLocaleLowerCase().includes(term);
            if (!item.hidden) count++;
        });
        categories.forEach(function (category) {
            var next = category.nextElementSibling;
            var visible = false;
            while (next && !next.classList.contains('side-item-category')) {
                if (next.matches('li.slide') && !next.hidden) visible = true;
                next = next.nextElementSibling;
            }
            category.hidden = !visible;
        });
        empty.hidden = count !== 0;
    });
    document.addEventListener('keydown', function (event) {
        var editing = event.target.closest('input, textarea, select, [contenteditable="true"]');
        if (event.key === '/' && !editing && !event.ctrlKey && !event.metaKey && !event.altKey && window.innerWidth >= 992) {
            event.preventDefault();
            input.focus();
        }
        if (event.key === 'Escape' && document.activeElement === input) {
            input.value = '';
            input.dispatchEvent(new Event('input'));
        }
    });
    menu.querySelectorAll('a.active').forEach(function (link) { link.setAttribute('aria-current', 'page'); });
    document.querySelectorAll('.table-responsive').forEach(function (region) {
        if (region.scrollWidth > region.clientWidth) {
            region.setAttribute('tabindex', '0');
        }
    });
})();

/* Remember an explicit motion preference; reduced-motion remains authoritative in CSS. */
(function () {
    'use strict';
    var buttons = document.querySelectorAll('[data-art-motion]');
    if (!buttons.length) return;
    var paused = false;
    try { paused = localStorage.getItem('workspaceArtMotion') === 'paused'; } catch (_) {}
    function paint() {
        document.body.dataset.artMotion = paused ? 'paused' : 'playing';
        buttons.forEach(function (button) {
            button.setAttribute('aria-pressed', String(paused));
            var label = paused ? button.dataset.playLabel : button.dataset.pauseLabel;
            button.setAttribute('aria-label', label); button.title = label;
        });
    }
    buttons.forEach(function (button) { button.addEventListener('click', function () {
        paused = !paused; paint();
        try { localStorage.setItem('workspaceArtMotion', paused ? 'paused' : 'playing'); } catch (_) {}
    }); });
    paint();
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) { entry.target.dataset.artVisible = String(entry.isIntersecting); });
        });
        document.querySelectorAll('.ip-page-art').forEach(function (art) { observer.observe(art); });
    }
})();
