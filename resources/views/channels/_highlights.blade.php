{{-- Pinned posts as a highlights strip (ADR-014). First tap: preview sheet with the summary; second tap: open the post.
     Expects $highlights (Collection<Post>). Optional: $channel (to mark posts inherited from a parent). Self-contained CSS/JS. --}}
@if (($highlights ?? collect())->isNotEmpty())
@once
<style>
    .hl-wrap { margin: 4px 0 18px; }
    .hl-title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; letter-spacing: .02em; text-transform: uppercase; opacity: .75; margin: 0 4px 10px; }
    .hl-strip { display: flex; gap: 12px; overflow-x: auto; scroll-snap-type: x mandatory; padding: 4px 4px 10px; scrollbar-width: none; }
    .hl-strip::-webkit-scrollbar { display: none; }
    .hl-item { flex: 0 0 auto; width: 132px; scroll-snap-align: start; cursor: pointer; border: 0; background: none; padding: 0; text-align: start; color: inherit; font: inherit; }
    .hl-thumb { position: relative; width: 132px; height: 176px; border-radius: 18px; overflow: hidden; background: linear-gradient(135deg, #6259ca, #ec4899); box-shadow: 0 6px 18px rgba(15,23,42,.18); transition: transform .25s ease, box-shadow .25s ease; }
    .hl-item:hover .hl-thumb, .hl-item:focus-visible .hl-thumb { transform: translateY(-3px); box-shadow: 0 12px 26px rgba(15,23,42,.26); }
    .hl-item.active .hl-thumb { outline: 3px solid #6259ca; outline-offset: 2px; }
    .hl-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .hl-thumb .hl-ic { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 40px; color: rgba(255,255,255,.9); }
    .hl-thumb::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, transparent 45%, rgba(0,0,0,.72)); }
    .hl-pin { position: absolute; top: 8px; inset-inline-start: 8px; z-index: 2; background: rgba(255,255,255,.92); color: #6259ca; border-radius: 999px; font-size: 11px; font-weight: 800; padding: 2px 8px; }
    .hl-cap { position: absolute; z-index: 2; inset-inline: 10px; bottom: 9px; color: #fff; font-size: 13px; font-weight: 700; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

    .hl-sheet { position: fixed; inset: 0; z-index: 2000; display: none; align-items: flex-end; justify-content: center; background: rgba(15,23,42,.55); backdrop-filter: blur(3px); }
    .hl-sheet.open { display: flex; animation: hlFade .2s ease; }
    .hl-panel { cursor: pointer; width: min(560px, 100%); max-height: 86vh; overflow: auto; background: #fff; color: #0f172a; border-radius: 22px 22px 0 0; padding: 0 0 20px; animation: hlUp .28s cubic-bezier(.2,.8,.2,1); }
    @media (min-width: 640px) { .hl-sheet { align-items: center; } .hl-panel { border-radius: 22px; } }
    [data-theme="dark"] .hl-panel, .dark-theme .hl-panel { background: #1e2740; color: #e5e9f5; }
    .hl-cover { height: 210px; background: linear-gradient(135deg, #6259ca, #ec4899) center/cover; position: relative; }
    .hl-close { position: absolute; top: 12px; inset-inline-end: 12px; width: 36px; height: 36px; border-radius: 50%; border: 0; background: rgba(0,0,0,.45); color: #fff; font-size: 20px; cursor: pointer; }
    .hl-body { padding: 18px 20px 0; }
    .hl-meta { font-size: 12px; opacity: .7; margin-bottom: 6px; }
    .hl-body h3 { font-size: 19px; font-weight: 800; margin: 0 0 8px; line-height: 1.35; }
    .hl-body p { font-size: 15px; line-height: 1.7; opacity: .88; margin: 0 0 16px; white-space: pre-line; }
    .hl-open { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; border-radius: 14px; background: #6259ca; color: #fff !important; font-weight: 700; text-decoration: none !important; }
    .hl-hint { text-align: center; font-size: 12px; opacity: .6; margin-top: 8px; }
    @keyframes hlUp { from { transform: translateY(40px); opacity: 0; } to { transform: none; opacity: 1; } }
    @keyframes hlFade { from { opacity: 0; } to { opacity: 1; } }
    @media (prefers-reduced-motion: reduce) { .hl-sheet.open, .hl-panel { animation: none; } .hl-thumb { transition: none; } }
</style>
@endonce

<div class="hl-wrap">
    <div class="hl-title">📌 {{ __('Highlights') }}</div>
    <div class="hl-strip" role="list">
        @foreach ($highlights as $hl)
            @php
                $thumb = $hl->highlightThumbUrl();
                $headline = $hl->title ?: \Illuminate\Support\Str::limit($hl->body, 60);
                $inherited = isset($channel) && $hl->channel_id !== $channel->id;
            @endphp
            <button type="button" class="hl-item" role="listitem"
                    data-url="{{ route('guest.post-detail', $hl->id) }}"
                    data-title="{{ $headline }}"
                    data-summary="{{ \Illuminate\Support\Str::limit($hl->summary ?: $hl->body, 320) }}"
                    data-thumb="{{ $thumb }}"
                    data-meta="{{ ($inherited ? $hl->channel?->name . ' · ' : '') . ($hl->published_at ?? $hl->created_at)->diffForHumans() }}"
                    aria-label="{{ $headline }}">
                <div class="hl-thumb">
                    @if ($thumb)<img src="{{ $thumb }}" alt="" loading="lazy">@else<span class="hl-ic">📣</span>@endif
                    <span class="hl-pin">📌</span>
                    <span class="hl-cap">{{ $headline }}</span>
                </div>
            </button>
        @endforeach
    </div>
</div>

@once
<div class="hl-sheet" id="hlSheet" aria-hidden="true">
    <div class="hl-panel" role="dialog" aria-modal="true" aria-labelledby="hlSheetTitle">
        <div class="hl-cover" id="hlSheetCover"><button type="button" class="hl-close" id="hlSheetClose" aria-label="{{ __('Close') }}">×</button></div>
        <div class="hl-body">
            <div class="hl-meta" id="hlSheetMeta"></div>
            <h3 id="hlSheetTitle"></h3>
            <p id="hlSheetSummary"></p>
            <a href="#" class="hl-open" id="hlSheetOpen">{{ __('Read the full post') }} →</a>
            <div class="hl-hint">{{ __('Tap again to read the full post') }}</div>
        </div>
    </div>
</div>
<script>
    (function () {
        var sheet = document.getElementById('hlSheet'), current = null;
        function close() {
            sheet.classList.remove('open'); sheet.setAttribute('aria-hidden', 'true');
            if (current) current.classList.remove('active');
            current = null;
        }
        function open(item) {
            if (current) current.classList.remove('active');
            current = item; item.classList.add('active');
            document.getElementById('hlSheetTitle').textContent = item.dataset.title;
            document.getElementById('hlSheetSummary').textContent = item.dataset.summary;
            document.getElementById('hlSheetMeta').textContent = item.dataset.meta;
            document.getElementById('hlSheetOpen').href = item.dataset.url;
            var cover = document.getElementById('hlSheetCover');
            cover.style.backgroundImage = item.dataset.thumb ? 'url("' + item.dataset.thumb + '")' : '';
            sheet.classList.add('open'); sheet.setAttribute('aria-hidden', 'false');
        }
        document.addEventListener('click', function (e) {
            var item = e.target.closest('.hl-item');
            if (item) {
                // Second tap on the same highlight opens the post.
                if (current === item && sheet.classList.contains('open')) { window.location.href = item.dataset.url; return; }
                open(item);
                return;
            }
            if (e.target === sheet || e.target.id === 'hlSheetClose') { close(); return; }
            // Second tap: anywhere on the preview opens the full post.
            if (current && e.target.closest('.hl-panel') && !e.target.closest('.hl-open')) window.location.href = current.dataset.url;
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    })();
</script>
@endonce
@endif
