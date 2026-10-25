{{-- Contact and location details. Rows are skipped when the organization has no value. --}}
@php
    $rows = array_filter([
        'map-pin' => collect([$organization->address, $organization->city, $organization->country])->filter()->implode(', '),
        'phone' => $organization->phone,
        'mail' => $organization->email,
    ]);
@endphp
@if ($rows)
    <h5 class="mb-3 mt-4">{{ __('About & contact') }}</h5>
    <div class="card"><div class="card-body">
        @if ($organization->description)<p class="text-muted">{{ $organization->description }}</p><hr>@endif
        <ul class="list-unstyled mb-0">
            @foreach ($rows as $icon => $value)
                <li class="d-flex align-items-center py-2">
                    <span class="avatar avatar-md brround bg-primary-transparent text-primary mx-2 d-inline-flex align-items-center justify-content-center"><i class="fe fe-{{ $icon }}"></i></span>
                    <span>{{ $value }}</span>
                </li>
            @endforeach
        </ul>
    </div></div>
@endif
