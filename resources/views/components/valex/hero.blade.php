@props(['title', 'subtitle' => null])

<div class="card bg-primary-gradient text-white">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
        <div class="mb-2 mb-md-0">
            <h3 class="text-white mb-1">{{ $title }}</h3>
            @if ($subtitle)<p class="mb-0 op-8">{{ $subtitle }}</p>@endif
            {{ $slot }}
        </div>
        @if (isset($actions))<div>{{ $actions }}</div>@endif
    </div>
</div>
