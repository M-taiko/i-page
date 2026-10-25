@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex flex-wrap align-items-center">
            <h4 class="content-title mb-0 my-auto">{{ $title }}</h4>
            @if ($subtitle)
                <span class="text-muted mt-1 tx-13 mx-2 mb-0">/ {{ $subtitle }}</span>
            @endif
        </div>
        @if (count($breadcrumbs))
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0 p-0 bg-transparent">
                    @foreach ($breadcrumbs as $label => $url)
                        @if ($url)
                            <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                        @else
                            <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>
    @if (isset($actions) && !$actions->isEmpty())
        <div class="d-flex my-xl-auto right-content">
            {{ $actions }}
        </div>
    @endif
</div>
