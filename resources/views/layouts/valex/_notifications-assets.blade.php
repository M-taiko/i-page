{{-- Styles and behavior of the dashboard notification bell (docs/27). Included once by the Valex layout. --}}
@push('styles')
<style>
    .dvn { position: relative; }
    .dvn-btn { position: relative; background: none; border: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .dvn-btn .header-icon-svgs { font-size: 20px; line-height: 1; }
    .dvn-btn.has-unread .header-icon-svgs { animation: dvnRing 4s ease-in-out infinite; transform-origin: 50% 10%; display: inline-block; }
    .dvn-badge { position: absolute; top: 4px; inset-inline-end: 2px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #f43f5e, #e11d48); color: #fff; font-size: 10.5px; font-weight: 800; line-height: 1; border: 2px solid #fff; box-shadow: 0 4px 10px -3px rgba(225, 29, 72, .6); }
    .dvn-badge[hidden] { display: none; }
    .dvn-badge.bump { animation: dvnBump .5s cubic-bezier(.34, 1.56, .64, 1); }
    .dvn-panel { position: absolute; top: calc(100% + 12px); inset-inline-end: -10px; z-index: 1100; width: 400px; max-width: calc(100vw - 24px);
        border-radius: 18px; overflow: hidden; background: #fff; color: #0f172a; border: 1px solid #e5e9f2;
        box-shadow: 0 28px 60px -20px rgba(15, 23, 42, .45); animation: dvnIn .2s cubic-bezier(.2, .8, .2, 1); transform-origin: top right; }
    [dir="rtl"] .dvn-panel { transform-origin: top left; }
    .dvn-panel[hidden] { display: none; }
    .dvn-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 16px 18px 12px; }
    .dvn-head strong { display: block; font-size: 16px; font-weight: 800; }
    .dvn-head small { display: block; margin-top: 2px; font-size: 12px; color: #64748b; }
    .dvn-all { border: 0; background: rgba(98, 89, 202, .1); color: #6259ca; font: inherit; font-size: 12.5px; font-weight: 700; padding: 7px 12px; border-radius: 10px; cursor: pointer; white-space: nowrap; }
    .dvn-all:hover { background: rgba(98, 89, 202, .18); }
    .dvn-list { max-height: min(62vh, 460px); overflow-y: auto; padding: 0 8px 8px; }
    .dvn-list .np-item { display: flex; gap: 12px; padding: 11px 12px; border-radius: 14px; text-decoration: none; color: #475569; position: relative; transition: background .15s; }
    .dvn-list .np-item:hover { background: #f1f5f9; }
    .dvn-list .np-item.is-unread { background: rgba(98, 89, 202, .07); color: #0f172a; }
    .dvn-list .np-ic { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--c); background: color-mix(in srgb, var(--c) 14%, transparent); font-size: 18px; }
    .dvn-list .np-body { min-width: 0; display: flex; flex-direction: column; gap: 3px; padding-inline-end: 14px; }
    .dvn-list .np-msg { font-size: 13.5px; line-height: 1.45; font-weight: 600; overflow-wrap: anywhere; }
    .dvn-list .np-time { font-size: 12px; color: #94a3b8; }
    .dvn-list .np-dot { position: absolute; top: 50%; inset-inline-end: 12px; width: 9px; height: 9px; margin-top: -4.5px; border-radius: 50%; background: #6259ca; }
    .dvn-list .np-empty { display: flex; flex-direction: column; align-items: center; gap: 4px; text-align: center; padding: 8px 24px 20px; color: #64748b; font-size: 13px; }
    .dvn-list .np-empty strong { color: #0f172a; font-size: 15px; }
    .dvn-foot { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 13px; border-top: 1px solid #e5e9f2; font-size: 13.5px; font-weight: 700; color: #6259ca; text-decoration: none; }
    .dvn-foot:hover { background: #f8fafc; color: #4f46e5; }
    /* Dark mode (Valex adds .dark-theme to <body>) */
    .dark-theme .dvn-panel { background: #1e2740; color: #e5e9f5; border-color: rgba(255, 255, 255, .08); }
    .dark-theme .dvn-head small, .dark-theme .dvn-list .np-time { color: #94a3b8; }
    .dark-theme .dvn-list .np-item { color: #b3bdd6; }
    .dark-theme .dvn-list .np-item:hover { background: rgba(255, 255, 255, .06); }
    .dark-theme .dvn-list .np-item.is-unread { background: rgba(124, 108, 255, .16); color: #f1f5f9; }
    .dark-theme .dvn-list .np-empty strong { color: #f1f5f9; }
    .dark-theme .dvn-foot { border-color: rgba(255, 255, 255, .08); color: #a5b4fc; }
    .dark-theme .dvn-foot:hover { background: rgba(255, 255, 255, .05); }
    .dark-theme .dvn-badge { border-color: #1e2740; }
    @media (max-width: 575px) { .dvn { position: static; } .dvn-panel { position: fixed; top: 62px; inset-inline: 10px; width: auto; max-width: none; } }
    @keyframes dvnRing { 0%, 86%, 100% { transform: rotate(0); } 88% { transform: rotate(14deg); } 91% { transform: rotate(-12deg); } 94% { transform: rotate(7deg); } 97% { transform: rotate(-3deg); } }
    @keyframes dvnBump { 0% { transform: scale(.6); } 100% { transform: scale(1); } }
    @keyframes dvnIn { from { opacity: 0; transform: translateY(-6px) scale(.97); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .dvn-btn.has-unread .header-icon-svgs, .dvn-badge.bump, .dvn-panel { animation: none; } }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const root = document.getElementById('dvNotif');
        if (!root) return;
        const btn = document.getElementById('dvNotifBtn');
        const panel = document.getElementById('dvNotifPanel');
        const list = document.getElementById('dvNotifList');
        const badge = document.getElementById('dvNotifBadge');
        const sub = document.getElementById('dvNotifSub');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const text = { none: @json(__('You\'re all caught up')), one: @json(__('1 unread')), many: @json(__(':count unread')) };

        function setCount(n) {
            const prev = parseInt(badge.textContent, 10) || 0;
            badge.hidden = n <= 0;
            badge.textContent = n > 99 ? '99+' : n;
            btn.classList.toggle('has-unread', n > 0);
            sub.textContent = n <= 0 ? text.none : (n === 1 ? text.one : text.many.replace(':count', n));
            if (n > prev) { badge.classList.remove('bump'); void badge.offsetWidth; badge.classList.add('bump'); }
        }
        async function refresh() {
            try {
                const res = await fetch(root.dataset.latestUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!res.ok) return;
                const data = await res.json();
                setCount(data.unread);
                list.innerHTML = data.html;
            } catch (e) { /* offline: keep what is shown */ }
        }
        function open() { panel.hidden = false; btn.setAttribute('aria-expanded', 'true'); refresh(); }
        function close() { panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); }

        btn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); panel.hidden ? open() : close(); });
        document.addEventListener('click', e => { if (!panel.hidden && !root.contains(e.target)) close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !panel.hidden) { close(); btn.focus(); } });

        // Opening a notification marks it read in the background, then follows its link.
        list.addEventListener('click', e => {
            const item = e.target.closest('.np-item');
            if (!item || !item.classList.contains('is-unread')) return;
            fetch(item.dataset.readUrl, { method: 'POST', keepalive: true, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
            item.classList.remove('is-unread');
            item.querySelector('.np-dot')?.remove();
            setCount(Math.max(0, (parseInt(badge.textContent, 10) || 1) - 1));
        });
        document.getElementById('dvNotifReadAll').addEventListener('click', async () => {
            list.querySelectorAll('.np-item.is-unread').forEach(i => { i.classList.remove('is-unread'); i.querySelector('.np-dot')?.remove(); });
            setCount(0);
            try { await fetch(root.dataset.readAllUrl, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } }); } catch (e) {}
        });

        setInterval(() => { if (document.visibilityState === 'visible') refresh(); }, 45000);
    })();
</script>
@endpush
