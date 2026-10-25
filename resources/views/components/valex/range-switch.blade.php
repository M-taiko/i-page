@props(['days', 'ranges' => \App\Services\DashboardStatsService::RANGES])

<div class="btn-group" role="group" aria-label="{{ __('Date range') }}">
    @foreach ($ranges as $range)
        <a href="{{ request()->fullUrlWithQuery(['range' => $range]) }}"
           class="btn btn-sm {{ (int) $days === $range ? 'btn-primary' : 'btn-outline-primary' }}">{{ $range }}{{ __('d') }}</a>
    @endforeach
</div>
