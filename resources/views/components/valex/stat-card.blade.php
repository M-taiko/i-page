@props(['title', 'value', 'icon' => 'activity', 'variant' => 'primary'])
<div class="card ip-stat-card">
    <div class="card-body d-flex align-items-center justify-content-between">
        <div><p class="ip-stat-label">{{ $title }}</p><h2 class="ip-stat-value">{{ $value }}</h2></div>
        <span class="avatar avatar-lg brround bg-{{ $variant }}-transparent text-{{ $variant }}"><i class="fe fe-{{ $icon }} tx-24" aria-hidden="true"></i></span>
    </div>
</div>
