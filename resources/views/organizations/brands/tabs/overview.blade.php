{{-- Brand overview: KPIs and charts scoped to this brand's channels (ADR-015). --}}
<div class="row row-sm">
    @foreach ($stats['kpis'] as $kpi)
        <div class="col-sm-6 col-xl-4 col-xxl-2">
            <x-valex.kpi-card :kpi="$kpi" :days="($kpi['current'] || $kpi['previous']) ? $days : null" />
        </div>
    @endforeach
</div>

<div class="row row-sm">
    <div class="col-xl-8">
        <x-valex.card :title="__('Posts')" :subtitle="__('Posts per day across the brand')">
            <div style="height: 280px;"><canvas id="growthChart"></canvas></div>
        </x-valex.card>
    </div>
    <div class="col-xl-4">
        <x-valex.card :title="__('Content Mix')" :subtitle="__('Posts by type')">
            <div style="height: 280px;"><canvas id="postTypesChart"></canvas></div>
        </x-valex.card>
    </div>
</div>

<div class="row row-sm">
    <div class="col-xl-4 col-md-6"><x-valex.engagement :engagement="$stats['engagement']" /></div>
    <div class="col-xl-5 col-md-6">
        <x-valex.card :title="__('QR Scans')" :subtitle="__('Scans of the brand channels per day')">
            <div style="height: 240px;"><canvas id="scansChart"></canvas></div>
        </x-valex.card>
    </div>
    <div class="col-xl-3">
        <x-valex.card :title="__('Devices')">
            <div style="height: 240px;"><canvas id="devicesChart"></canvas></div>
        </x-valex.card>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-6">
        <x-valex.card :title="__('Largest channels')">
            <x-slot:actions><a href="{{ route('organizations.brands.show', [$organization, $brand, 'tab' => 'channels']) }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a></x-slot:actions>
            <ul class="list-group list-group-flush">
                @forelse ($stats['topChannels'] as $channel)
                    <li class="list-group-item d-flex align-items-center px-0">
                        <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2"><i class="fe fe-{{ $channel->type === 'private' ? 'lock' : 'message-circle' }}"></i></span>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <a href="{{ route('tenant.channels.show', $channel) }}" class="font-weight-bold">{{ $channel->name }}</a>
                            <div class="small text-muted">{{ $channel->posts_count }} {{ __('posts') }}</div>
                        </div>
                        <x-valex.badge variant="success">{{ $channel->users_count }} {{ __('members') }}</x-valex.badge>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">{{ __('No channels under this brand yet.') }}</li>
                @endforelse
            </ul>
        </x-valex.card>
    </div>
    <div class="col-lg-6">
        <x-valex.card :title="__('Latest posts')">
            <x-slot:actions><a href="{{ route('organizations.brands.show', [$organization, $brand, 'tab' => 'posts']) }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a></x-slot:actions>
            @include('organizations.brands.tabs._post-list', ['posts' => $latestPosts])
        </x-valex.card>
    </div>
</div>

@php $chartData = Arr::only($stats, ['growth', 'scans', 'devices', 'postTypes']); @endphp
@push('scripts')
<script src="{{ asset('assets/valex/plugins/chart.js/Chart.bundle.min.js') }}"></script>
<script src="{{ asset('assets/valex/js/ipage-charts.js') }}"></script>
<script>
    (function () {
        var s = @json($chartData);
        var none = @json(__('No data for this period'));
        IPChart.line('growthChart', s.growth, none);
        IPChart.doughnut('postTypesChart', s.postTypes.labels, s.postTypes.data, none);
        IPChart.line('scansChart', s.scans, none);
        IPChart.doughnut('devicesChart', s.devices.labels, s.devices.data, none);
    })();
</script>
@endpush
