@props(['kpi', 'days' => null])

@php
    $change = $kpi['change'];
    $up = $change >= 0;
@endphp
<div class="card custom-card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted tx-12 text-uppercase">{{ $kpi['label'] }}</span>
                <h3 class="mb-0 mt-1 font-weight-bold">{{ number_format($kpi['total']) }}</h3>
            </div>
            <span class="avatar avatar-lg brround bg-{{ $kpi['variant'] }}-transparent text-{{ $kpi['variant'] }}">
                <i class="fe fe-{{ $kpi['icon'] }} tx-20"></i>
            </span>
        </div>
        @if ($days)
            <div class="mt-3 tx-12 text-muted">
                <span class="font-weight-bold {{ $up ? 'text-success' : 'text-danger' }}">
                    <i class="fe fe-arrow-{{ $up ? 'up' : 'down' }}-right"></i> {{ abs($change) }}%
                </span>
                <span class="mx-1">{{ number_format($kpi['current']) }} {{ __('in last :d days', ['d' => $days]) }}</span>
            </div>
        @endif
    </div>
</div>
