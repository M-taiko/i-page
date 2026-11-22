@once
<style>
    /* Post formats (ADR-013). Self-contained so it works on the classic mobile shell and the Valex pages. */
    .pf-content { --pf-accent: #6259ca; }
    .pf-badges { display: flex; flex-wrap: wrap; gap: 6px; padding: 0 16px 8px; }
    .pf-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; color: #fff; background: var(--pf-accent); }
    .pf-badge.pf-chip { background: rgba(100,116,139,.15); color: #475569; font-weight: 600; }
    .pf-badge.pf-prio-high { background: #f59e0b; }
    .pf-badge.pf-prio-critical { background: #dc2626; }
    [data-theme="dark"] .pf-badge.pf-chip, .dark-theme .pf-badge.pf-chip { color: #cbd5e1; background: rgba(148,163,184,.2); }

    .pf-body { padding: 0 16px 12px; font-size: 14px; line-height: 1.6; white-space: pre-line; }
    .pf-title { font-weight: 700; font-size: 16px; padding: 0 16px 6px; line-height: 1.4; }
    .pf-content a.pf-link { color: inherit; text-decoration: none; display: block; }

    /* Compact (default template): bold title and a few lines of text beside a rounded thumbnail. */
    .pf-row { display: flex; align-items: flex-start; gap: 14px; padding: 0 16px 12px; }
    .pf-row-text { flex: 1; min-width: 0; }
    .pf-style-compact .pf-title { padding: 0 0 4px; font-size: 17px; font-weight: 800; line-height: 1.3; letter-spacing: -.01em;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .pf-style-compact .pf-body { padding: 0; font-size: 14px; line-height: 1.5; white-space: normal; opacity: .82;
        display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .pf-row.has-thumb .pf-body { -webkit-line-clamp: 3; }
    .pf-thumb { position: relative; flex: 0 0 38%; max-width: 190px; aspect-ratio: 16 / 10; border-radius: 14px; overflow: hidden; display: block; background: rgba(100,116,139,.12); }
    .pf-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .35s ease; }
    .pf-thumb:hover img { transform: scale(1.05); }
    .pf-thumb-more { position: absolute; inset-inline-end: 6px; bottom: 6px; padding: 1px 8px; border-radius: 999px; background: rgba(15,23,42,.7); color: #fff; font-size: 11.5px; font-weight: 700; }
    @media (min-width: 640px) { .pf-thumb { max-width: 210px; } }
    @media (max-width: 380px) { .pf-thumb { flex-basis: 34%; } .pf-style-compact .pf-title { font-size: 16px; } }
    @media (prefers-reduced-motion: reduce) { .pf-thumb img { transition: none; } }

    .pf-style-image .pf-media img.pf-single, .pf-style-image .pf-carousel { max-height: 360px; }
    .pf-style-gradient .pf-text { margin: 0 16px 12px; padding: 22px 20px; border-radius: 14px; color: #fff;
        background: linear-gradient(135deg, var(--pf-accent), #1e293b); }
    .pf-style-gradient .pf-title { padding: 0 0 8px; color: #fff; font-size: 18px; }
    .pf-style-gradient .pf-body { padding: 0; color: rgba(255,255,255,.92); }
    .pf-style-spotlight { border-inline-start: 4px solid var(--pf-accent); border-radius: 12px; background: rgba(98,89,202,.05); padding-top: 12px; }

    .pf-media { margin: 0 0 12px; }
    img.pf-single { width: 100%; max-height: 420px; object-fit: cover; display: block; }
    .pf-carousel { position: relative; overflow: hidden; background: #0f172a; }
    .pf-track { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
    .pf-track::-webkit-scrollbar { display: none; }
    .pf-slide { flex: 0 0 100%; scroll-snap-align: center; }
    .pf-slide img { width: 100%; height: 320px; object-fit: cover; display: block; }
    .pf-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; border-radius: 50%; border: 0; background: rgba(0,0,0,.45); color: #fff; font-size: 18px; line-height: 1; cursor: pointer; z-index: 2; }
    .pf-nav.pf-prev { inset-inline-start: 8px; } .pf-nav.pf-next { inset-inline-end: 8px; }
    .pf-dots { position: absolute; bottom: 8px; left: 0; right: 0; display: flex; justify-content: center; gap: 6px; }
    .pf-dots span { width: 7px; height: 7px; border-radius: 50%; background: rgba(255,255,255,.5); }
    .pf-dots span.on { background: #fff; }

    .pf-widgets { padding: 0 16px 12px; }
    .pf-box { border: 1px solid rgba(100,116,139,.25); border-radius: 12px; padding: 14px; margin-bottom: 10px; }
    .pf-box h6 { margin: 0 0 10px; font-size: 13px; font-weight: 700; }
    .pf-muted { color: #64748b; font-size: 12px; }
    [data-theme="dark"] .pf-muted, .dark-theme .pf-muted { color: #94a3b8; }
    .pf-alert { background: rgba(220,38,38,.1); color: #b91c1c; padding: 8px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 10px; }
    .pf-ok { background: rgba(34,197,94,.12); color: #15803d; padding: 8px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 10px; }

    .pf-react { display: flex; flex-wrap: wrap; gap: 6px; }
    .pf-react button, .pf-react a { display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 999px; border: 1px solid rgba(100,116,139,.3); background: transparent; color: inherit; font-size: 13px; cursor: pointer; text-decoration: none; }
    .pf-react .on { background: var(--pf-accent); border-color: var(--pf-accent); color: #fff; }

    .pf-opt { display: flex; align-items: center; gap: 8px; padding: 9px 12px; border: 1px solid rgba(100,116,139,.3); border-radius: 10px; margin-bottom: 8px; cursor: pointer; }
    .pf-res { position: relative; border: 1px solid rgba(100,116,139,.25); border-radius: 10px; margin-bottom: 8px; overflow: hidden; padding: 9px 12px; display: flex; justify-content: space-between; }
    .pf-res .pf-bar { position: absolute; inset: 0 auto 0 0; background: var(--pf-accent); opacity: .18; }
    [dir="rtl"] .pf-res .pf-bar { inset: 0 0 0 auto; }
    .pf-res span { position: relative; } .pf-res.mine { border-color: var(--pf-accent); font-weight: 700; }

    .pf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 16px; border-radius: 9px; border: 1px solid var(--pf-accent); background: var(--pf-accent); color: #fff; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; }
    .pf-btn.pf-outline { background: transparent; color: var(--pf-accent); }
    .pf-btn.on { background: var(--pf-accent); color: #fff; }
    .pf-row { display: flex; flex-wrap: wrap; gap: 8px; }

    .pf-stars { display: inline-flex; gap: 2px; direction: ltr; }
    .pf-stars button { background: none; border: 0; padding: 0 1px; font-size: 26px; line-height: 1; cursor: pointer; color: #cbd5e1; }
    .pf-stars button.on { color: #f59e0b; }
    .pf-starline { color: #f59e0b; letter-spacing: 1px; }
</style>
<script>
    // Tiny dependency-free carousel for gallery posts (scroll-snap + buttons + dots).
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.pf-nav'); if (!btn) return;
        var box = btn.closest('.pf-carousel'), track = box.querySelector('.pf-track');
        var dir = (btn.classList.contains('pf-next') ? 1 : -1) * (getComputedStyle(track).direction === 'rtl' ? -1 : 1);
        track.scrollBy({ left: dir * track.clientWidth, behavior: 'smooth' });
    });
    document.addEventListener('scroll', function (e) {
        var track = e.target; if (!track.classList || !track.classList.contains('pf-track')) return;
        var i = Math.round(Math.abs(track.scrollLeft) / track.clientWidth);
        track.parentNode.querySelectorAll('.pf-dots span').forEach(function (d, n) { d.classList.toggle('on', n === i); });
    }, true);
    // Auto-advance (gallery autoplay); pauses while the user is touching/hovering.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.pf-carousel[data-autoplay="1"]').forEach(function (box) {
            var track = box.querySelector('.pf-track'), paused = false, ms = (parseInt(box.dataset.interval, 10) || 4) * 1000;
            ['mouseenter', 'touchstart'].forEach(function (ev) { box.addEventListener(ev, function () { paused = true; }, { passive: true }); });
            ['mouseleave', 'touchend'].forEach(function (ev) { box.addEventListener(ev, function () { paused = false; }, { passive: true }); });
            setInterval(function () {
                if (paused) return;
                var max = track.scrollWidth - track.clientWidth;
                if (Math.abs(track.scrollLeft) >= max - 2) track.scrollTo({ left: 0, behavior: 'smooth' });
                else track.scrollBy({ left: (getComputedStyle(track).direction === 'rtl' ? -1 : 1) * track.clientWidth, behavior: 'smooth' });
            }, ms);
        });
    });
</script>
@endonce
