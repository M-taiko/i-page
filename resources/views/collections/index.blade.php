@extends('layouts.mobile-shell')

@section('title', __('Collections') . ' - i-Page')

@section('app-bar')
    <a href="{{ route('user.feed') }}" class="app-bar-icon-btn" aria-label="{{ __('Back') }}">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="app-bar-title">{{ __('Collections') }}</div>
    <div class="app-bar-actions">
        <button type="button" class="app-bar-icon-btn" onclick="openCreateModal()" aria-label="{{ __('New Collection') }}">
            <i class="bi bi-plus-lg"></i>
        </button>
    </div>
@endsection

@section('extra-styles')
    .collections-grid { padding: var(--space-4); display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: var(--space-3); }

    .collection-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: var(--space-4);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: var(--space-2);
        cursor: pointer;
        position: relative;
    }

    .collection-card-icon {
        width: 56px; height: 56px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: relative;
    }

    .collection-card-icon.pinned::before {
        content: ''; position: absolute; inset: -3px; border-radius: 50%;
        border: 2px solid var(--warning-500);
    }

    .collection-card-name { font-weight: var(--font-weight-semibold); font-size: var(--text-sm); color: var(--text-primary); }
    .collection-card-meta { font-size: var(--text-xs); color: var(--text-tertiary); }

    .collection-card-menu {
        position: absolute; top: var(--space-2); right: var(--space-2);
        background: none; border: none; color: var(--text-tertiary); cursor: pointer; padding: 4px;
    }

    .empty-collections { text-align: center; padding: var(--space-10) var(--space-4); color: var(--text-secondary); grid-column: 1 / -1; }

    /* Bottom sheet + modal (shared pattern) */
    .sheet-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); z-index: 60; align-items: flex-end; justify-content: center; }
    .sheet-overlay.show { display: flex; }
    .bottom-sheet { background-color: var(--surface-bg); width: 100%; max-width: 480px; border-radius: 20px 20px 0 0; padding: var(--space-3) 0 max(var(--space-4), env(safe-area-inset-bottom)); }
    .sheet-handle { width: 40px; height: 4px; background-color: var(--surface-border); border-radius: var(--radius-full); margin: 0 auto var(--space-3); }
    .sheet-title { display: flex; align-items: center; gap: var(--space-2); padding: 0 var(--space-4) var(--space-3); font-weight: var(--font-weight-bold); color: var(--text-primary); }
    .sheet-title .sheet-icon-preview { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .sheet-action { display: flex; align-items: center; gap: var(--space-3); width: 100%; padding: var(--space-3) var(--space-4); background: none; border: none; text-align: start; cursor: pointer; font-size: var(--text-sm); color: var(--text-primary); }
    .sheet-action:hover { background-color: var(--surface-hover); }
    .sheet-action i { font-size: var(--text-lg); color: var(--text-secondary); width: 22px; }
    .sheet-action.danger { color: var(--danger-600); }
    .sheet-action.danger i { color: var(--danger-600); }

    .cmodal-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); z-index: 60; align-items: center; justify-content: center; padding: var(--space-4); }
    .cmodal-overlay.show { display: flex; }
    .cmodal { background-color: var(--surface-bg); border-radius: var(--radius-xl); padding: var(--space-6); max-width: 360px; width: 100%; max-height: 85vh; overflow-y: auto; }
    .cmodal h3 { font-size: var(--text-lg); margin-bottom: var(--space-4); color: var(--text-primary); }
    .cmodal-preview { width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto var(--space-4); }
    .cmodal label { display: block; font-size: var(--text-xs); font-weight: var(--font-weight-semibold); color: var(--text-secondary); margin-bottom: var(--space-2); text-transform: uppercase; }
    .cmodal input[type="text"] { width: 100%; padding: var(--space-3); border: 1px solid var(--surface-border); border-radius: var(--radius-md); font-size: var(--text-sm); background-color: var(--surface-bg-secondary); color: var(--text-primary); margin-bottom: var(--space-4); }
    .icon-grid, .color-grid { display: flex; flex-wrap: wrap; gap: var(--space-2); margin-bottom: var(--space-4); }
    .icon-swatch { width: 40px; height: 40px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 18px; background-color: var(--surface-bg-secondary); border: 2px solid transparent; cursor: pointer; }
    .icon-swatch.selected { border-color: var(--primary-600); background-color: var(--primary-50); }
    .color-swatch { width: 32px; height: 32px; border-radius: 50%; cursor: pointer; border: 3px solid transparent; }
    .color-swatch.selected { border-color: var(--text-primary); }
    .cmodal-actions { display: flex; gap: var(--space-3); margin-top: var(--space-2); }
    .cmodal-actions button { flex: 1; padding: var(--space-3); border-radius: var(--radius-md); font-weight: var(--font-weight-medium); font-size: var(--text-sm); cursor: pointer; border: none; }
    .cmodal-actions .btn-cancel { background-color: var(--surface-hover); color: var(--text-primary); }
    .cmodal-actions .btn-save { background-color: var(--primary-600); color: white; }

    /* Add-channels modal */
    .achannel-row { display: flex; align-items: center; gap: var(--space-3); padding: var(--space-2) 0; border-bottom: 1px solid var(--surface-border); }
    .achannel-avatar { width: 36px; height: 36px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); display: flex; align-items: center; justify-content: center; color: white; font-weight: var(--font-weight-bold); font-size: var(--text-xs); flex-shrink: 0; }
    .achannel-name { flex: 1; font-size: var(--text-sm); color: var(--text-primary); }
@endsection

@section('content')
    <div class="collections-grid">
        @forelse($collections as $collection)
            <div class="collection-card"
                 data-id="{{ $collection->id }}"
                 data-name="{{ $collection->name }}"
                 data-icon="{{ $collection->icon }}"
                 data-color="{{ $collection->color }}"
                 data-pinned="{{ $collection->is_pinned ? '1' : '0' }}"
                 data-muted="{{ $collection->is_muted ? '1' : '0' }}"
                 onclick="goToCollection({{ $collection->id }})">
                <button type="button" class="collection-card-menu" onclick="event.stopPropagation(); openSheet(this.closest('.collection-card'))">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div class="collection-card-icon {{ $collection->is_pinned ? 'pinned' : '' }}" style="background-color: {{ $collection->color }};">
                    {{ $collection->icon }}
                </div>
                <div class="collection-card-name">{{ $collection->name }}</div>
                <div class="collection-card-meta">
                    {{ $collection->channels_count }} {{ __('channels') }}
                    @if($collection->is_muted) · <i class="bi bi-bell-slash-fill"></i> @endif
                </div>
            </div>
        @empty
            <div class="empty-collections">
                <i class="bi bi-folder2-open" style="font-size: 2.5rem; display: block; margin-bottom: var(--space-3); opacity: 0.5;"></i>
                <p style="margin-bottom: var(--space-3);">{{ __('No collections yet') }}</p>
                <button type="button" onclick="openCreateModal()" style="background-color: var(--primary-600); color: white; border: none; border-radius: var(--radius-full); padding: var(--space-2) var(--space-5); font-weight: var(--font-weight-medium); cursor: pointer;">
                    {{ __('Create your first Collection') }}
                </button>
            </div>
        @endforelse
    </div>
@endsection

@section('modals')
    <!-- Bottom sheet -->
    <div class="sheet-overlay" id="collectionSheet" onclick="if(event.target===this) closeSheet()">
        <div class="bottom-sheet">
            <div class="sheet-handle"></div>
            <div class="sheet-title">
                <span class="sheet-icon-preview" id="sheetIconPreview"></span>
                <span id="sheetCollectionName"></span>
            </div>
            <button type="button" class="sheet-action" onclick="openManageChannels()">
                <i class="bi bi-collection"></i> {{ __('Manage Channels') }}
            </button>
            <button type="button" class="sheet-action" onclick="openEditModal('name')">
                <i class="bi bi-pencil"></i> {{ __('Rename') }}
            </button>
            <button type="button" class="sheet-action" onclick="openEditModal('icon')">
                <i class="bi bi-emoji-smile"></i> {{ __('Change Icon') }}
            </button>
            <button type="button" class="sheet-action" onclick="openEditModal('color')">
                <i class="bi bi-palette"></i> {{ __('Change Color') }}
            </button>
            <button type="button" class="sheet-action" id="sheetPinAction" onclick="sheetTogglePin()">
                <i class="bi bi-pin-angle"></i> <span id="sheetPinLabel">{{ __('Pin Collection') }}</span>
            </button>
            <button type="button" class="sheet-action" id="sheetMuteAction" onclick="sheetToggleMute()">
                <i class="bi bi-bell-slash"></i> <span id="sheetMuteLabel">{{ __('Mute Notifications') }}</span>
            </button>
            <button type="button" class="sheet-action danger" onclick="sheetDelete()">
                <i class="bi bi-trash"></i> {{ __('Delete') }}
            </button>
        </div>
    </div>

    <!-- Create / Edit modal -->
    <div class="cmodal-overlay" id="collectionModal" onclick="if(event.target===this) closeCreateModal()">
        <div class="cmodal">
            <h3 id="cmodalTitle">{{ __('New Collection') }}</h3>
            <div class="cmodal-preview" id="cmodalPreview" style="background-color: #4557f5;">💼</div>

            <form id="collectionForm" method="POST" onsubmit="submitCollectionForm(event)">
                @csrf
                <input type="hidden" name="_method" id="cmodalMethod" value="POST">
                <input type="hidden" name="icon" id="cmodalIconInput" value="💼">
                <input type="hidden" name="color" id="cmodalColorInput" value="#4557f5">

                <label>{{ __('Name') }}</label>
                <input type="text" name="name" id="cmodalNameInput" placeholder="{{ __('e.g. Work, Shopping, University') }}" maxlength="60" required>

                <label>{{ __('Icon') }}</label>
                <div class="icon-grid" id="cmodalIconGrid">
                    @foreach($icons as $icon)
                        <div class="icon-swatch" data-icon="{{ $icon }}" onclick="selectIcon('{{ $icon }}')">{{ $icon }}</div>
                    @endforeach
                </div>

                <label>{{ __('Color') }}</label>
                <div class="color-grid" id="cmodalColorGrid">
                    @foreach($colors as $hex => $label)
                        <div class="color-swatch" data-color="{{ $hex }}" style="background-color: {{ $hex }};" title="{{ $label }}" onclick="selectColor('{{ $hex }}')"></div>
                    @endforeach
                </div>

                <div class="cmodal-actions">
                    <button type="button" class="btn-cancel" onclick="closeCreateModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-save">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manage channels modal -->
    <div class="cmodal-overlay" id="manageChannelsModal" onclick="if(event.target===this) closeManageChannels()">
        <div class="cmodal">
            <h3>{{ __('Add Channels') }}</h3>
            <p style="font-size: var(--text-xs); color: var(--text-tertiary); margin-bottom: var(--space-3);">
                {{ __('Only your subscribed channels can be added to a collection.') }}
            </p>
            <div id="achannelList">
                @forelse($subscribedChannels as $channel)
                    <div class="achannel-row" data-channel-id="{{ $channel->id }}">
                        <div class="achannel-avatar">{{ substr($channel->name, 0, 1) }}</div>
                        <div class="achannel-name">{{ $channel->name }}</div>
                        <input type="checkbox" class="achannel-toggle" data-channel-id="{{ $channel->id }}" onchange="toggleChannelInCollection(this)">
                    </div>
                @empty
                    <p style="font-size: var(--text-sm); color: var(--text-tertiary);">
                        {{ __('You have no subscribed channels yet.') }}
                        <a href="{{ route('user.explore-channels') }}" style="color: var(--primary-600);">{{ __('Explore channels') }}</a>
                    </p>
                @endforelse
            </div>
            <div class="cmodal-actions">
                <button type="button" class="btn-save" style="flex: 1;" onclick="closeManageChannels()">{{ __('Done') }}</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const collectionsBaseUrl = "{{ url('/collections') }}";
    const collectionsStoreUrl = "{{ route('collections.store') }}";

    function csrfToken() {
        return document.querySelector('#collectionForm input[name="_token"]').value;
    }

    async function apiRequest(url, method, body) {
        const isForm = body instanceof FormData;
        if (isForm) { body.append('_token', csrfToken()); }
        return fetch(url, {
            method: 'POST',
            headers: isForm ? { 'X-Requested-With': 'XMLHttpRequest' } : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'X-Requested-With': 'XMLHttpRequest' },
            body: isForm ? body : JSON.stringify(body || {}),
        });
    }

    function goToCollection(id) {
        window.location.href = `${collectionsBaseUrl}/${id}`;
    }

    let activeCollection = null;

    function openSheet(card) {
        activeCollection = {
            id: card.dataset.id, name: card.dataset.name, icon: card.dataset.icon,
            color: card.dataset.color, pinned: card.dataset.pinned === '1', muted: card.dataset.muted === '1',
        };
        document.getElementById('sheetIconPreview').textContent = activeCollection.icon;
        document.getElementById('sheetIconPreview').style.backgroundColor = activeCollection.color;
        document.getElementById('sheetCollectionName').textContent = activeCollection.name;
        document.getElementById('sheetPinLabel').textContent = activeCollection.pinned ? '{{ __('Unpin Collection') }}' : '{{ __('Pin Collection') }}';
        document.getElementById('sheetMuteLabel').textContent = activeCollection.muted ? '{{ __('Unmute Notifications') }}' : '{{ __('Mute Notifications') }}';
        document.getElementById('collectionSheet').classList.add('show');
    }

    function closeSheet() { document.getElementById('collectionSheet').classList.remove('show'); }

    async function sheetTogglePin() {
        await apiRequest(`${collectionsBaseUrl}/${activeCollection.id}/pin`, 'POST', new FormData());
        closeSheet(); window.location.reload();
    }

    async function sheetToggleMute() {
        await apiRequest(`${collectionsBaseUrl}/${activeCollection.id}/mute`, 'POST', new FormData());
        closeSheet(); window.location.reload();
    }

    async function sheetDelete() {
        if (!confirm('{{ __('Delete this collection? Channels stay subscribed, only the folder is removed.') }}')) return;
        const fd = new FormData(); fd.append('_method', 'DELETE');
        await apiRequest(`${collectionsBaseUrl}/${activeCollection.id}`, 'POST', fd);
        closeSheet(); window.location.reload();
    }

    let modalMode = 'create';

    function openCreateModal() {
        modalMode = 'create';
        document.getElementById('cmodalTitle').textContent = '{{ __('New Collection') }}';
        document.getElementById('cmodalMethod').value = 'POST';
        document.getElementById('collectionForm').action = collectionsStoreUrl;
        document.getElementById('cmodalNameInput').value = '';
        selectIcon('💼'); selectColor('#4557f5');
        document.getElementById('collectionModal').classList.add('show');
    }

    function openEditModal(focusField) {
        closeSheet();
        modalMode = 'edit';
        document.getElementById('cmodalTitle').textContent = '{{ __('Edit Collection') }}';
        document.getElementById('cmodalMethod').value = 'PUT';
        document.getElementById('collectionForm').action = `${collectionsBaseUrl}/${activeCollection.id}`;
        document.getElementById('cmodalNameInput').value = activeCollection.name;
        selectIcon(activeCollection.icon); selectColor(activeCollection.color);
        document.getElementById('collectionModal').classList.add('show');
    }

    function closeCreateModal() { document.getElementById('collectionModal').classList.remove('show'); }

    function selectIcon(icon) {
        document.getElementById('cmodalIconInput').value = icon;
        document.getElementById('cmodalPreview').textContent = icon;
        document.querySelectorAll('#cmodalIconGrid .icon-swatch').forEach(s => s.classList.toggle('selected', s.dataset.icon === icon));
    }

    function selectColor(color) {
        document.getElementById('cmodalColorInput').value = color;
        document.getElementById('cmodalPreview').style.backgroundColor = color;
        document.querySelectorAll('#cmodalColorGrid .color-swatch').forEach(s => s.classList.toggle('selected', s.dataset.color === color));
    }

    async function submitCollectionForm(event) {
        event.preventDefault();
        const form = document.getElementById('collectionForm');
        const fd = new FormData(form);
        if (modalMode === 'edit') { fd.append('_method', 'PUT'); }
        await fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        closeCreateModal();
        window.location.reload();
    }

    const channelCollectionMap = @json($channelCollectionMap ?? []);

    function openManageChannels() {
        closeSheet();
        document.getElementById('manageChannelsModal').dataset.collectionId = activeCollection.id;
        document.querySelectorAll('#achannelList .achannel-toggle').forEach(cb => {
            const memberships = channelCollectionMap[cb.dataset.channelId] || [];
            cb.checked = memberships.includes(Number(activeCollection.id));
        });
        document.getElementById('manageChannelsModal').classList.add('show');
    }

    function closeManageChannels() {
        document.getElementById('manageChannelsModal').classList.remove('show');
    }

    async function toggleChannelInCollection(checkbox) {
        const collectionId = document.getElementById('manageChannelsModal').dataset.collectionId;
        const channelId = checkbox.dataset.channelId;
        const url = `${collectionsBaseUrl}/${collectionId}/channels/${channelId}`;
        const fd = new FormData();
        if (!checkbox.checked) { fd.append('_method', 'DELETE'); }
        await apiRequest(url, 'POST', fd);
    }
</script>
@endsection
