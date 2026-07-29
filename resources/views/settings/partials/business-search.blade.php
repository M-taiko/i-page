<div class="settings-card" style="padding: var(--space-4); position: relative;">
    <p style="font-size: var(--text-sm); color: var(--text-secondary); margin: 0 0 var(--space-3);">
        {{ __('Search for your organization and send a request to join as staff.') }}
    </p>
    <div style="position: relative;">
        <input type="text" id="orgJoinSearchInput" autocomplete="off" class="settings-input"
               style="border: 1px solid var(--surface-border); border-radius: var(--radius-md); padding: var(--space-2) var(--space-3); width: 100%;"
               placeholder="{{ __('e.g. Cairo University') }}">
        <div id="orgJoinSearchResults" style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 20; background-color: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-md); box-shadow: var(--shadow-lg, 0 8px 24px rgba(0,0,0,0.12)); max-height: 320px; overflow-y: auto;"></div>
    </div>
</div>
