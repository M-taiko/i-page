@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($actions))
        <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    @if ($title)<h4 class="card-title mb-0">{{ $title }}</h4>@endif
                    @if ($subtitle)<p class="tx-12 tx-gray-500 mb-0 mt-1">{{ $subtitle }}</p>@endif
                </div>
                @if (isset($actions)){{ $actions }}@endif
            </div>
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
