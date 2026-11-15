{{-- Audit trail for the brand, its channels and posts in them (ADR-015). --}}
<x-valex.card :title="__('Activity')" :subtitle="__('Changes to :brand, its channels and their posts.', ['brand' => $brand->name])">
    @include('crm._activity-table', ['logs' => $logs])
    <div class="mt-3">{{ $logs->links() }}</div>
</x-valex.card>
