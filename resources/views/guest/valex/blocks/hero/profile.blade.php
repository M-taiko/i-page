{{-- Avatar-led profile card (Valex "profile" + "avatar" pattern). --}}
<div class="card">
    <div class="bg-primary-gradient" style="height: 90px; border-radius: .25rem .25rem 0 0;"></div>
    <div class="card-body text-center" style="margin-top: -56px;">
        <span class="avatar avatar-xxl brround bg-white text-primary d-inline-flex align-items-center justify-content-center font-weight-bold tx-28 border"
              style="width: 96px; height: 96px; border-width: 4px !important;">{{ mb_substr($organization->name, 0, 1) }}</span>
        <h4 class="mt-3 mb-1">{{ $organization->name }}</h4>
        @if ($organization->city)<p class="text-muted mb-1"><i class="fe fe-map-pin"></i> {{ collect([$organization->city, $organization->country])->filter()->implode(', ') }}</p>@endif
        @if ($organization->description)<p class="text-muted mx-auto" style="max-width: 560px;">{{ Str::limit($organization->description, 200) }}</p>@endif
        <div class="mb-3">@include('guest.valex.blocks._actions')</div>
        <div class="border-top pt-3">@include('guest.valex.blocks._stats')</div>
    </div>
</div>
