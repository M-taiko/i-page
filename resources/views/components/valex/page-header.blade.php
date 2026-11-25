@props(['title', 'subtitle' => null, 'breadcrumbs' => [], 'illustration' => null])
<div class="breadcrumb-header ip-page-header justify-content-between">
    <div class="ip-page-heading-group">
        <x-valex.page-art :scene="$illustration" />
    <div class="ip-page-heading">
        @if (count($breadcrumbs))
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-2 p-0 bg-transparent">
                @foreach ($breadcrumbs as $label => $url)
                    @if ($url)<li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                    @else<li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>@endif
                @endforeach
            </ol></nav>
        @endif
        <h1 class="content-title mb-0">{{ $title }}</h1>
        @if ($subtitle)<p class="ip-page-subtitle mb-0">{{ $subtitle }}</p>@endif
    </div>
    </div>
    @if (isset($actions) && !$actions->isEmpty())
        <div class="d-flex right-content ip-page-actions">{{ $actions }}</div>
    @endif
</div>
