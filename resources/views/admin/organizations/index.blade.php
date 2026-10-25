@extends('layouts.valex')

@section('title', __('Organizations Management'))

@section('page-header')
    <x-valex.page-header
        :title="__('Organizations')"
        :subtitle="__('Manage all organizations in the system')">
        <x-slot:actions>
            <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary">
                <i class="fe fe-plus"></i> {{ __('Add Organization') }}
            </a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
    <div class="row row-sm">
        <div class="col-sm-6 col-xl-4">
            <x-valex.stat-card :title="__('Total Organizations')" :value="$organizations->total()" icon="briefcase" variant="primary" />
        </div>
        <div class="col-sm-6 col-xl-4">
            <x-valex.stat-card :title="__('Active')" :value="$organizations->where('is_active', true)->count()" icon="check-circle" variant="success" />
        </div>
        <div class="col-sm-6 col-xl-4">
            <x-valex.stat-card :title="__('Inactive')" :value="$organizations->where('is_active', false)->count()" icon="x-circle" variant="danger" />
        </div>
    </div>

    @if ($organizations->count() > 0)
        <x-valex.card>
            <div class="table-responsive">
                <table class="table table-hover text-md-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Organization') }}</th>
                            <th class="text-center">{{ __('Max Channels') }}</th>
                            <th class="text-center">{{ __('Users') }}</th>
                            <th class="text-center">{{ __('Channels') }}</th>
                            <th class="text-center">{{ __('Posts') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($organizations as $org)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-md brround bg-primary mx-2">{{ mb_substr($org->name, 0, 1) }}</span>
                                        <div>
                                            <strong class="d-block">{{ $org->name }}</strong>
                                            <small class="text-muted">{{ $org->description ? Str::limit($org->description, 30) : __('No description') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><x-valex.badge variant="primary">{{ $org->max_channels }}</x-valex.badge></td>
                                <td class="text-center"><x-valex.badge variant="info">{{ $org->users()->count() }}</x-valex.badge></td>
                                <td class="text-center"><x-valex.badge variant="warning">{{ $org->channels()->count() }}</x-valex.badge></td>
                                <td class="text-center"><x-valex.badge variant="success">{{ $org->posts()->count() }}</x-valex.badge></td>
                                <td class="text-center">
                                    @if ($org->is_active)
                                        <x-valex.badge variant="success">{{ __('Active') }}</x-valex.badge>
                                    @else
                                        <x-valex.badge variant="danger">{{ __('Inactive') }}</x-valex.badge>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('admin.organizations.show', $org->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('View') }}"><i class="fe fe-eye"></i></a>
                                    <a href="{{ route('admin.organizations.edit', $org->id) }}" class="btn btn-sm btn-outline-warning" title="{{ __('Edit') }}"><i class="fe fe-edit"></i></a>
                                    <form action="{{ route('admin.organizations.destroy', $org->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}" onclick="return confirm('{{ __('Are you sure?') }}')"><i class="fe fe-trash-2"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($organizations->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $organizations->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </x-valex.card>
    @else
        <x-valex.empty-state :title="__('No Organizations Yet')" :message="__('Start by creating your first organization.')" icon="briefcase">
            <a href="{{ route('admin.organizations.create') }}" class="btn btn-primary mt-3">
                <i class="fe fe-plus"></i> {{ __('Create First Organization') }}
            </a>
        </x-valex.empty-state>
    @endif
@endsection
