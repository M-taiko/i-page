{{-- Feed card helpers and infinite scroll for partials.feed._list. --}}
<script>
    document.documentElement.classList.add('js');

    /* Greeting and day/night scene follow the visitor's own clock, not the server's. */
    (function () {
        const h = new Date().getHours();
        const part = h >= 5 && h < 12 ? 'morning' : (h >= 12 && h < 17 ? 'afternoon' : 'evening');
        document.querySelectorAll('.js-greeting').forEach(el => { el.textContent = el.dataset[part]; });
        const day = h >= 5 && h < 17;
        document.querySelectorAll('[data-daytime]').forEach(el => { el.toggleAttribute('hidden', (el.dataset.daytime === 'day') !== day); });
    })();


    /* Save (bookmark) a post without reloading. */
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-save-url]');
        if (!btn || btn.disabled) return;
        e.preventDefault();
        btn.disabled = true;
        try {
            const res = await fetch(btn.dataset.saveUrl, { method: 'POST', credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } });
            if (!res.ok) throw new Error(res.status);
            const saved = (await res.json()).saved;
            btn.classList.toggle('is-on', saved);
            btn.setAttribute('aria-pressed', saved ? 'true' : 'false');
            btn.dataset.tip = saved ? '{{ __('Saved') }}' : '{{ __('Save') }}';
            btn.querySelector('i').className = 'bi ' + (saved ? 'bi-bookmark-fill' : 'bi-bookmark');
        } catch (err) { /* keep the current state */ }
        finally { btn.disabled = false; }
    });

    function toggleFeedComments(button) {
        const box = button.closest('.fc').querySelector('.fc-comments');
        if (!box) return;
        box.hidden = !box.hidden;
        button.setAttribute('aria-expanded', String(!box.hidden));
        if (!box.hidden) box.querySelector('input')?.focus();
    }

    function sharePost(url, button) {
        if (navigator.share) { navigator.share({ url }).catch(() => {}); return; }
        navigator.clipboard?.writeText(url);
        const icon = button.querySelector('i');
        icon.className = 'bi bi-check2';
        setTimeout(() => { icon.className = 'bi bi-send'; }, 1500);
    }

    /* Infinite scroll: fetch the next batch as HTML when the sentinel comes into view. */
    (function () {
        const feed = document.getElementById('feed');
        if (!feed) return;
        const loading = document.getElementById('feedLoading');
        const error = document.getElementById('feedError');
        const end = document.getElementById('feedEnd');
        const sentinel = document.getElementById('feedSentinel');
        let next = feed.dataset.next || null;
        let busy = false;

        window.feedLoad = async function () {
            if (!next || busy) return;
            busy = true;
            error.hidden = true;
            loading.hidden = false;
            try {
                const url = new URL(next, window.location.href);
                url.searchParams.set('partial', '1');
                const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (!res.ok) throw new Error(res.status);
                const data = await res.json();
                const holder = document.createElement('div');
                holder.innerHTML = data.html;
                const added = Array.from(holder.children);
                added.forEach(el => feed.appendChild(el));
                window.ipReveal && window.ipReveal(feed);
                next = data.next;
                if (!next) { end.hidden = false; observer && observer.disconnect(); }
            } catch (e) {
                error.hidden = false;
            } finally {
                loading.hidden = true;
                busy = false;
            }
        };

        const observer = 'IntersectionObserver' in window
            ? new IntersectionObserver(entries => { if (entries[0].isIntersecting) feedLoad(); }, { rootMargin: '700px 0px' })
            : null;
        if (observer && next) observer.observe(sentinel);
        if (!observer) document.querySelectorAll('.feed-more-link').forEach(el => el.style.display = 'flex');
    })();
</script>
