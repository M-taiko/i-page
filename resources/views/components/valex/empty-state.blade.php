@props(['title', 'message' => null, 'icon' => 'inbox'])

<div class="card">
    <div class="card-body text-center py-5">
        <i class="fe fe-{{ $icon }} tx-40 text-muted"></i>
        <h4 class="mt-3">{{ $title }}</h4>
        @if ($message)<p class="text-muted">{{ $message }}</p>@endif
        {{ $slot }}
    </div>
</div>
