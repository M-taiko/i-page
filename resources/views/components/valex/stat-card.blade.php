@props(['title', 'value', 'icon' => 'activity', 'variant' => 'primary'])

<div class="card overflow-hidden sales-card bg-{{ $variant }}-gradient">
    <div class="px-3 pt-3 pb-2">
        <h6 class="mb-3 tx-12 text-white">{{ $title }}</h6>
        <div class="d-flex align-items-center justify-content-between">
            <h4 class="tx-22 font-weight-bold mb-1 text-white">{{ $value }}</h4>
            <i class="fe fe-{{ $icon }} tx-24 text-white op-7"></i>
        </div>
    </div>
    <span id="compositeline-{{ \Illuminate\Support\Str::slug($title) }}" class="pt-1"></span>
</div>
