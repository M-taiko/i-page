{{-- Unfollow confirmation (docs/27). Lists the channels the visitor joined themselves and offers to leave
     them too (checked by default), so nothing from this organization stays in the feed unless they want it.
     Expects $organization. Opened by any element with data-open-unfollow="{{ $organization->id }}". --}}
@php
    $ownChannels = \App\Models\Channel::where('organization_id', $organization->id)
        ->whereIn('id', \Illuminate\Support\Facades\DB::table('channel_user')->where('user_id', auth()->id())->where('source', 'self')->select('channel_id'))
        ->orderBy('name')->get(['id', 'name', 'type']);
    $followCount = \Illuminate\Support\Facades\DB::table('channel_user')->where('user_id', auth()->id())->where('source', 'follow')
        ->whereIn('channel_id', \App\Models\Channel::where('organization_id', $organization->id)->select('id'))->count();
@endphp
<dialog class="uf" id="unfollow-{{ $organization->id }}" aria-labelledby="ufTitle{{ $organization->id }}">
    <form method="POST" action="{{ route('organizations.unfollow', $organization->id) }}" class="uf-card">
        @csrf
        <div class="uf-ill"><x-illustration name="channels" size="96" /></div>
        <h3 id="ufTitle{{ $organization->id }}">{{ __('Unfollow :name?', ['name' => $organization->name]) }}</h3>
        <p>{{ trans_choice('You\'ll leave its :count main channel and its posts will leave your feed.|You\'ll leave its :count main channels and their posts will leave your feed.', $followCount, ['count' => $followCount]) }}</p>

        @if ($ownChannels->isNotEmpty())
            <label class="uf-check">
                <input type="checkbox" name="leave_own" value="1" checked>
                <span>
                    <strong>{{ trans_choice('Also leave the :count channel you joined yourself|Also leave the :count channels you joined yourself', $ownChannels->count(), ['count' => $ownChannels->count()]) }}</strong>
                    <small>{{ __('Uncheck to keep them in your feed with their notifications.') }}</small>
                </span>
            </label>
            <ul class="uf-list">
                @foreach ($ownChannels->take(6) as $ch)
                    <li><i class="bi bi-{{ $ch->type === 'private' ? 'lock' : 'broadcast' }}"></i> {{ $ch->name }}</li>
                @endforeach
                @if ($ownChannels->count() > 6)<li class="uf-more">+{{ $ownChannels->count() - 6 }}</li>@endif
            </ul>
        @endif

        <div class="uf-actions">
            <button type="button" class="uf-btn uf-cancel" data-close-unfollow>{{ __('Cancel') }}</button>
            <button type="submit" class="uf-btn uf-danger"><i class="bi bi-person-dash"></i> {{ __('Unfollow') }}</button>
        </div>
    </form>
</dialog>

@once
<style>
    .uf { border: 0; padding: 0; margin: auto; background: transparent; max-width: min(420px, calc(100vw - 24px)); width: 100%; color: inherit; }
    .uf::backdrop { background: rgba(15, 23, 42, .55); -webkit-backdrop-filter: blur(3px); backdrop-filter: blur(3px); }
    .uf[open] { animation: ufIn .22s cubic-bezier(.2, .8, .2, 1); }
    @keyframes ufIn { from { opacity: 0; transform: translateY(12px) scale(.98); } to { opacity: 1; transform: none; } }
    .uf-card { margin: 0; padding: 22px 20px 18px; border-radius: 24px; text-align: center; background: var(--surface-bg, #fff); color: var(--text-primary, #0f172a); box-shadow: 0 30px 70px -20px rgba(15, 23, 42, .5); }
    .uf-ill { display: flex; justify-content: center; margin-bottom: 4px; }
    .uf-card h3 { margin: 6px 0 6px; font-size: 18px; font-weight: 800; }
    .uf-card p { margin: 0 0 14px; font-size: 13.5px; line-height: 1.6; color: var(--text-secondary, #475569); }
    .uf-check { display: flex; gap: 10px; align-items: flex-start; text-align: start; padding: 12px; border-radius: 14px; background: var(--surface-hover, #f1f5f9); cursor: pointer; }
    .uf-check input { margin-top: 3px; width: 18px; height: 18px; accent-color: #6d5dfc; flex: 0 0 auto; }
    .uf-check strong { display: block; font-size: 13.5px; }
    .uf-check small { display: block; margin-top: 2px; font-size: 12px; color: var(--text-tertiary, #64748b); }
    .uf-list { list-style: none; margin: 8px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }
    .uf-list li { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; border: 1px solid var(--surface-border, #e2e8f0); font-size: 12px; font-weight: 600; }
    .uf-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 18px; }
    .uf-btn { padding: 12px; border-radius: 14px; border: 0; cursor: pointer; font: inherit; font-size: 14px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
    .uf-cancel { background: var(--surface-hover, #f1f5f9); color: var(--text-primary, #0f172a); }
    .uf-danger { background: #e11d48; color: #fff; }
    .uf-danger:hover { background: #be123c; }
    @media (prefers-reduced-motion: reduce) { .uf[open] { animation: none; } }
</style>
<script>
    document.addEventListener('click', function (e) {
        const opener = e.target.closest('[data-open-unfollow]');
        if (opener) {
            const dialog = document.getElementById('unfollow-' + opener.dataset.openUnfollow);
            if (dialog && typeof dialog.showModal === 'function') { e.preventDefault(); dialog.showModal(); }
            return;
        }
        if (e.target.closest('[data-close-unfollow]')) e.target.closest('dialog').close();
        if (e.target.tagName === 'DIALOG' && e.target.classList.contains('uf')) e.target.close();   // click on the backdrop
    });
</script>
@endonce
