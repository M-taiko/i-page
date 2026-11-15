{{-- The brand's channel tree at every depth (ADR-015), e.g. Brand → Country → Department → Sub-department. --}}
<x-valex.card :title="__('Channel tree')" :subtitle="__('Every channel of :brand and how they nest. Brand posts reach them according to their audience.', ['brand' => $brand->name])">
    @if ($canManage)
        <x-slot:actions><a href="{{ route('tenant.channels.create') }}" class="btn btn-sm btn-outline-primary"><i class="fe fe-plus"></i> {{ __('New Channel') }}</a></x-slot:actions>
    @endif
    @if ($tree)
        <ul class="list-unstyled mb-0 brand-tree">
            @foreach ($tree as $node)
                @include('organizations.brands.tabs._tree-node', ['node' => $node, 'depth' => 0])
            @endforeach
        </ul>
    @else
        <p class="text-center text-muted mb-0">{{ __('No channels under this brand yet.') }}</p>
    @endif
</x-valex.card>

@once
@push('styles')
<style>
    .brand-tree ul { list-style: none; padding-inline-start: 22px; margin: 0; border-inline-start: 2px dashed rgba(100,116,139,.25); margin-inline-start: 18px; }
    .brand-tree .tree-row { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; padding: 8px 6px; border-radius: 10px; }
    .brand-tree .tree-row:hover { background: rgba(100,116,139,.08); }
</style>
@endpush
@endonce
