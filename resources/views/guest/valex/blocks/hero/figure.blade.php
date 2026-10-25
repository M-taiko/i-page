{{-- Cover figure with caption overlay (Valex "images" figure/figcaption). The cover is a carousel when 2+ images exist. --}}
<figure class="org-hero-fig position-relative mb-3 text-white"
        style="min-height: {{ $options['cover']['height_px'] }}px; background: linear-gradient(135deg, var(--org-primary), var(--org-secondary));">
    @include('guest.valex.blocks._cover')
    <figcaption class="position-absolute w-100 p-4 d-flex align-items-end flex-wrap justify-content-between"
                style="bottom: 0; top: 0; z-index: 2; background: linear-gradient(180deg, rgba(0,0,0,.05), rgba(0,0,0,.65));">
        <div class="d-flex align-items-center">
            <span class="main-img-user avatar-xl mx-3 d-flex align-items-center justify-content-center bg-white text-primary rounded-circle font-weight-bold tx-24" style="width:72px;height:72px;">{{ mb_substr($organization->name, 0, 1) }}</span>
            <div>
                <h3 class="text-white mb-1">{{ $organization->name }}</h3>
                @if ($organization->description)<p class="mb-0 tx-white-8 d-none d-md-block">{{ Str::limit($organization->description, 140) }}</p>@endif
                @if ($organization->city)<small><i class="fe fe-map-pin"></i> {{ collect([$organization->city, $organization->country])->filter()->implode(', ') }}</small>@endif
            </div>
        </div>
        <div class="mt-3 mt-md-0">@include('guest.valex.blocks._actions')</div>
    </figcaption>
</figure>

@if ($options['show_stats'])
    <div class="card"><div class="card-body py-3">@include('guest.valex.blocks._stats')</div></div>
@endif
