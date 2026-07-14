<!-- Empty State Component -->
<div class="text-center py-5">
    @if($icon ?? false)
        <i class="bi bi-{{ $icon }}" style="font-size: 3rem; color: #ccc;"></i>
    @endif
    <h5 class="mt-3 text-muted">{{ $title ?? 'No data found' }}</h5>
    @if($message ?? false)
        <p class="text-muted">{{ $message }}</p>
    @endif
    @if($action ?? false)
        <div class="mt-3">
            {{ $action }}
        </div>
    @endif
</div>
