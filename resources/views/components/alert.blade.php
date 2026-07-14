<!-- Alert Component -->
<div class="alert alert-{{ $type ?? 'info' }} alert-dismissible fade show {{ $class ?? '' }}" role="alert">
    @if($icon ?? false)
        <i class="bi bi-{{ $icon }} me-2"></i>
    @endif
    {{ $slot }}
    @if($dismissible ?? true)
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    @endif
</div>
