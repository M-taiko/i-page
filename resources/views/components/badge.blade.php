<!-- Badge Component -->
<span class="badge bg-{{ $color ?? 'primary' }} {{ $class ?? '' }}">
    @if($icon ?? false)
        <i class="bi bi-{{ $icon }} me-1"></i>
    @endif
    {{ $slot }}
</span>
