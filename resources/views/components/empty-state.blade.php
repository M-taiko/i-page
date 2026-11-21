<!-- Empty State Component -->
<div class="text-center py-5">
    <x-illustration :name="$illustration ?? (str_contains($icon ?? '', 'search') ? 'search' : (str_contains($icon ?? '', 'bell') ? 'notifications' : 'empty'))" size="160" />
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
