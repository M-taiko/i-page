<!-- Card Component -->
<div class="card {{ $class ?? '' }}">
    @if($header ?? false)
        <div class="card-header bg-light">
            <h6 class="card-title mb-0">{{ $header }}</h6>
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>

    @if($footer ?? false)
        <div class="card-footer bg-light">
            {{ $footer }}
        </div>
    @endif
</div>
