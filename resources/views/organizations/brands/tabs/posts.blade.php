{{-- Every post across the brand (ADR-015). --}}
<x-valex.card :title="__('Posts across the brand')" :subtitle="__('Brand posts reach the channels chosen at publish time; channel posts stay in their channel (and sub-channels when cascaded).')">
    @if ($canManage)
        <x-slot:actions><a href="{{ route('organizations.brands.posts.create', [$organization, $brand]) }}" class="btn btn-sm btn-primary"><i class="fe fe-send"></i> {{ __('New brand post') }}</a></x-slot:actions>
    @endif
    @include('organizations.brands.tabs._post-list', ['posts' => $posts])
    <div class="mt-3">{{ $posts->links() }}</div>
</x-valex.card>
