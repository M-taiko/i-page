{{-- Reusable Add-to-Collection modal. Include on any authenticated page.
     Call openAddToCollection('channel'|'organization'|'brand', id, name) to open it. --}}
@auth
<style>
    .atc-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center; padding: var(--space-4); }
    .atc-overlay.show { display: flex; }
    .atc-box { background-color: var(--surface-bg); border-radius: var(--radius-xl); padding: var(--space-6); max-width: 360px; width: 100%; max-height: 80vh; overflow-y: auto; }
    .atc-box h3 { font-size: var(--text-lg); margin-bottom: var(--space-1); color: var(--text-primary); }
    .atc-box p.atc-target { font-size: var(--text-sm); color: var(--text-tertiary); margin-bottom: var(--space-4); }
    .atc-row { display: flex; align-items: center; gap: var(--space-3); padding: var(--space-2); border: 1px solid var(--surface-border); border-radius: var(--radius-md); margin-bottom: var(--space-2); }
    .atc-row-icon { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .atc-row-name { flex: 1; font-size: var(--text-sm); color: var(--text-primary); }
    .atc-empty { font-size: var(--text-sm); color: var(--text-tertiary); }
    .atc-btn-primary { width: 100%; padding: var(--space-3); border-radius: var(--radius-md); background-color: var(--primary-600); color: white; border: none; font-weight: 500; cursor: pointer; }
</style>

<div class="atc-overlay" id="addToCollectionOverlay" onclick="if(event.target===this) closeAddToCollection()">
    <div class="atc-box">
        <h3>{{ __('Add to Collection') }}</h3>
        <p class="atc-target" id="atcTargetName"></p>

        <div id="atcList">
            @forelse(auth()->user()->collections()->orderBy('sort_order')->get() as $collection)
                <div class="atc-row">
                    <span class="atc-row-icon" style="background-color: {{ $collection->color }};">{{ $collection->icon }}</span>
                    <span class="atc-row-name">{{ $collection->name }}</span>
                    <input type="checkbox" class="atc-checkbox" data-collection-id="{{ $collection->id }}" onchange="atcToggle(this)">
                </div>
            @empty
                <p class="atc-empty">{{ __("You don't have any collections yet. Create one from the Home page.") }}</p>
            @endforelse
        </div>

        <button type="button" class="atc-btn-primary" onclick="closeAddToCollection()">{{ __('Done') }}</button>
    </div>
</div>

<script>
    const atcBaseUrl = "{{ url('/collections') }}";
    const atcCsrfToken = "{{ csrf_token() }}";
    const atcChannelMap = @json(
        collect(auth()->user()->collections()->with('channels:id')->get())
            ->flatMap(fn($c) => collect($c->channels)->pluck('id')->map(fn($cid) => [$cid, $c->id]))
            ->groupBy(fn($pair) => $pair[0])
            ->map(fn($pairs) => $pairs->pluck(1))
    );

    let atcTarget = null; // { type, id, removable }

    function openAddToCollection(type, id, name) {
        atcTarget = { type, id, removable: type === 'channel' };
        document.getElementById('atcTargetName').textContent = name;

        document.querySelectorAll('.atc-checkbox').forEach(cb => {
            cb.disabled = false;
            if (type === 'channel') {
                const memberships = atcChannelMap[id] || [];
                cb.checked = memberships.includes(Number(cb.dataset.collectionId));
            } else {
                // Organization/Brand bulk-add is a one-way action; reset to unchecked each open.
                cb.checked = false;
            }
        });

        document.getElementById('addToCollectionOverlay').classList.add('show');
    }

    function closeAddToCollection() {
        document.getElementById('addToCollectionOverlay').classList.remove('show');
    }

    async function atcToggle(checkbox) {
        const collectionId = checkbox.dataset.collectionId;
        const endpointType = atcTarget.type === 'organization' ? 'organizations' : (atcTarget.type === 'brand' ? 'brands' : 'channels');
        const url = `${atcBaseUrl}/${collectionId}/${endpointType}/${atcTarget.id}`;

        const fd = new FormData();
        fd.append('_token', atcCsrfToken);

        if (!atcTarget.removable) {
            // One-way bulk add: lock the checkbox after firing so it can't be "unchecked" locally without a matching backend action.
            checkbox.disabled = true;
        } else if (!checkbox.checked) {
            fd.append('_method', 'DELETE');
        }

        await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
    }
</script>
@endauth
