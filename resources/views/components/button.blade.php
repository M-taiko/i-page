<!-- Button Component -->
<button type="{{ $type ?? 'button' }}" class="btn btn-{{ $variant ?? 'primary' }} {{ $class ?? '' }}" {{ $attributes }}>
    @if($icon ?? false)
        <i class="bi bi-{{ $icon }} me-1"></i>
    @endif
    {{ $slot }}
</button>
