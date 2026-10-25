@props(['engagement'])

<x-valex.card :title="__('Post Engagement')" :subtitle="number_format($engagement['total']) . ' ' . __('deliveries')">
    @if ($engagement['total'] === 0)
        <p class="text-muted text-center my-4">{{ __('No post deliveries in this period') }}</p>
    @else
        @foreach ($engagement['steps'] as $step)
            @php $variant = ['primary', 'info', 'success', 'warning'][$loop->index % 4]; @endphp
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">{{ $step['label'] }}</span>
                    <strong>{{ number_format($step['count']) }} <small class="text-muted">({{ $step['pct'] }}%)</small></strong>
                </div>
                <div class="progress" style="height: 8px;"><div class="progress-bar bg-{{ $variant }}" style="width: {{ $step['pct'] }}%"></div></div>
            </div>
        @endforeach
    @endif
</x-valex.card>
