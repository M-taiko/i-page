@extends('layouts.app-modern')

@section('content')
<style>
    .settings-header {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: 2rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
    }

    .settings-tabs {
        display: flex;
        gap: 1rem;
        border-bottom: 2px solid var(--surface-border);
        margin-bottom: 2rem;
        overflow-x: auto;
        padding-bottom: 0;
    }

    .settings-tabs .nav-link {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        border: none;
        border-bottom: 3px solid transparent;
        color: var(--text-secondary);
        font-weight: 500;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .settings-tabs .nav-link:hover {
        color: var(--text-primary);
        border-bottom-color: var(--primary-300);
    }

    .settings-tabs .nav-link.active {
        color: var(--primary-600);
        border-bottom-color: var(--primary-600);
    }

    .settings-content {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .settings-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .settings-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    }

    .form-label {
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border: 1px solid var(--surface-border);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-600);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn-primary {
        background: var(--primary-600);
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: var(--primary-700);
        transform: translateY(-2px);
    }

    .table-responsive {
        border-radius: 0 0 12px 12px;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead {
        background: var(--surface-bg-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: background 0.3s ease;
        border-color: var(--surface-border);
    }

    .table tbody tr:hover {
        background: var(--surface-bg-secondary);
    }

    .badge {
        padding: 0.5rem 0.75rem;
        font-weight: 500;
        border-radius: 6px;
    }
</style>

<div class="container-lg py-4">
    <!-- Header -->
    <div class="settings-header">
        <h1 class="mb-2">{{ $organization->name }}</h1>
        <p class="mb-0">Manage your organization settings, team, brands, and more</p>
    </div>

    <!-- Navigation Tabs -->
    <nav class="nav settings-tabs" role="tablist">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" role="tab">
            <i class="bi bi-gear"></i> General
        </button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#members" role="tab">
            <i class="bi bi-people"></i> Members
        </button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#brands" role="tab">
            <i class="bi bi-box-seam"></i> Brands
        </button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#locations" role="tab">
            <i class="bi bi-geo-alt"></i> Locations
        </button>
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sla" role="tab">
            <i class="bi bi-clock-history"></i> SLA Rules
        </button>
    </nav>

    <!-- Tab Content -->
    <div class="tab-content settings-content">
        <!-- General Settings -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="settings-card">
                <div class="card-header bg-light p-4 border-0 rounded-top-3">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Organization Settings</h5>
                </div>
                <div class="card-body p-4">
                            <form action="{{ route('organizations.update', $organization) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label">Organization Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $organization->name }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ $organization->email }}">
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="{{ $organization->phone }}">
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" value="{{ $organization->address }}">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" value="{{ $organization->city }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" value="{{ $organization->country }}">
                                    </div>
                                </div>

                                <button class="btn btn-primary" type="submit">Save Changes</button>
                            </form>
                </div>
            </div>
        </div>

        <!-- Members -->
        <div class="tab-pane fade" id="members" role="tabpanel">
            <div class="settings-card">
                <div class="card-header bg-light p-4 border-0 rounded-top-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Team Members</h5>
                </div>
                <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($organization->users()->get() as $user)
                                        <tr>
                                            <td>{{ $user->full_name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @php
                                                    $role = $user->organizations()
                                                        ->where('organization_id', $organization->id)
                                                        ->first()?->pivot->role ?? 'member';
                                                @endphp
                                                <span class="badge bg-primary">{{ ucfirst($role) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">No members yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                </div>
            </div>
        </div>

        <!-- Brands -->
        <div class="tab-pane fade" id="brands" role="tabpanel">
            <div class="settings-card">
                <div class="card-header bg-light p-4 border-0 rounded-top-3">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Brands</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($organization->brands as $brand)
                                <tr>
                                    <td>{{ $brand->name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $brand->is_active ? 'success' : 'secondary' }}">
                                            {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">No brands yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Locations -->
        <div class="tab-pane fade" id="locations" role="tabpanel">
            <div class="settings-card">
                <div class="card-header bg-light p-4 border-0 rounded-top-3">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Locations</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>City</th>
                                <th>Country</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($organization->locations as $location)
                                <tr>
                                    <td>{{ $location->name }}</td>
                                    <td>{{ $location->city }}</td>
                                    <td>{{ $location->country }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No locations yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SLA Rules -->
        <div class="tab-pane fade" id="sla" role="tabpanel">
            <div class="settings-card">
                <div class="card-header bg-light p-4 border-0 rounded-top-3">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> SLA Rules</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>First Response</th>
                                <th>Resolution</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($organization->slaRules as $rule)
                                <tr>
                                    <td>{{ $rule->name }}</td>
                                    <td>{{ $rule->first_response_time ? $rule->first_response_time . ' min' : '—' }}</td>
                                    <td>{{ $rule->resolution_time ? $rule->resolution_time . ' min' : '—' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                            {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No SLA rules yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
