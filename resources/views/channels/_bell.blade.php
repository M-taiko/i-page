{{-- Notification bell for a channel the visitor is subscribed to (docs/27). Toggles without reloading.
     Expects $channel, $bellOn; optional $class for the button. --}}
<button type="button" class="{{ $class ?? 'ch-bell' }} {{ $bellOn ? 'is-on' : '' }}"
        data-bell-url="{{ route('subscriptions.bell', $channel->id) }}"
        aria-pressed="{{ $bellOn ? 'true' : 'false' }}"
        aria-label="{{ $bellOn ? __('Notifications on — tap to mute') : __('Notifications off — tap to turn on') }}"
        data-tip="{{ $bellOn ? __('Notifications on') : __('Notifications muted') }}"
        data-tip-sub="{{ $bellOn ? __('Tap to mute this channel') : __('Tap to get notified about new posts') }}"
        data-on-label="{{ __('Notifications on') }}" data-off-label="{{ __('Notifications muted') }}"
        data-on-sub="{{ __('Tap to mute this channel') }}" data-off-sub="{{ __('Tap to get notified about new posts') }}">
    <i class="bi {{ $bellOn ? 'bi-bell-fill' : 'bi-bell-slash' }}"></i>
</button>
@once
<script>
    /* Bell toggle: POST, then flip the icon, label and tooltip. */
    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-bell-url]');
        if (!btn || btn.disabled) return;
        e.preventDefault();
        btn.disabled = true;
        try {
            const res = await fetch(btn.dataset.bellUrl, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            });
            if (!res.ok) throw new Error(res.status);
            const on = (await res.json()).on;
            btn.classList.toggle('is-on', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
            btn.querySelector('i').className = 'bi ' + (on ? 'bi-bell-fill' : 'bi-bell-slash');
            btn.dataset.tip = on ? btn.dataset.onLabel : btn.dataset.offLabel;
            btn.dataset.tipSub = on ? btn.dataset.onSub : btn.dataset.offSub;
            btn.classList.remove('ring'); void btn.offsetWidth; if (on) btn.classList.add('ring');
        } catch (err) {
            window.location.reload();
        } finally {
            btn.disabled = false;
        }
    });
</script>
@endonce
