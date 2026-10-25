@extends('layouts.valex')

@section('title', __('Audience Segments'))

@section('page-header')
    <x-valex.page-header :title="__('Audience Segments')" :subtitle="__('Precisely target who receives your posts and messages')">
        <x-slot:actions>
            @can('create', App\Models\AudienceSegment::class)
                <a href="{{ route('audience-segments.create') }}" class="btn btn-primary"><i class="fe fe-plus"></i> {{ __('New Segment') }}</a>
            @endcan
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@if ($segments->isEmpty())
    <x-valex.empty-state :title="__('No audience segments yet')" :message="__('Create one to start targeting specific groups of users.')" icon="users" />
@else
    <x-valex.card>
        <div class="table-responsive">
            <table class="table table-hover text-md-nowrap mb-0">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Description') }}</th><th>{{ __('Rules') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                <tbody>
                    @foreach ($segments as $segment)
                        @php
                            $rules = is_array($segment->rules) ? $segment->rules : (json_decode($segment->rules, true) ?? []);
                            $ruleCount = count(array_filter($rules, fn ($r) => ! empty($r['value'])));
                        @endphp
                        <tr>
                            <td class="font-weight-bold">{{ $segment->name }}</td>
                            <td class="text-muted">{{ $segment->description ?? '—' }}</td>
                            <td><x-valex.badge variant="primary"><i class="fe fe-filter"></i> {{ trans_choice('{1} :count rule|[0,*] :count rules', $ruleCount, ['count' => $ruleCount]) }}</x-valex.badge></td>
                            <td><x-valex.badge :variant="$segment->is_active ? 'success' : 'secondary'">{{ $segment->is_active ? __('Active') : __('Inactive') }}</x-valex.badge></td>
                            <td class="text-right text-nowrap">
                                @can('update', $segment)
                                    <a href="{{ route('audience-segments.edit', $segment) }}" class="btn btn-sm btn-outline-warning" title="{{ __('Edit') }}"><i class="fe fe-edit"></i></a>
                                @endcan
                                @can('delete', $segment)
                                    <form action="{{ route('audience-segments.destroy', $segment) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this segment?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}"><i class="fe fe-trash-2"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-valex.card>

    <div class="d-flex justify-content-center mt-4">{{ $segments->links('pagination::bootstrap-4') }}</div>
@endif
@endsection
