@if ($options['show_stats'])
    <div class="row text-center">
        @foreach ([[__('Members'), $organization->users_count], [__('Channels'), $organization->channels_count], [__('Posts'), $organization->posts_count]] as [$label, $value])
            <div class="col-4"><h4 class="mb-0 font-weight-bold">{{ number_format($value) }}</h4><small class="text-muted text-uppercase">{{ $label }}</small></div>
        @endforeach
    </div>
@endif
