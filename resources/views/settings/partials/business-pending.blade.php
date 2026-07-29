<div class="settings-card" style="padding: var(--space-4); text-align: center; color: var(--text-secondary); font-size: var(--text-sm);">
    <i class="bi bi-hourglass-split" style="font-size: 1.5rem; display: block; margin-bottom: var(--space-2); color: var(--primary-600);"></i>
    <span id="businessPendingMessage">{{ __('Your request to join :name is pending approval.', ['name' => $organizationName]) }}</span>
    <div style="margin-top: var(--space-3);">
        <button type="button" id="cancelJoinRequestBtn" data-org-id="{{ $organizationId }}" class="save-btn" style="background-color: var(--danger-600, #dc2626);">
            <i class="bi bi-x-lg"></i> {{ __('Cancel Request') }}
        </button>
    </div>
</div>
