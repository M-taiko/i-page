@extends('layouts.app-modern')

@section('content')
<div class="container-lg py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-4">{{ isset($rule) ? 'Edit SLA Rule' : 'Create SLA Rule' }}</h1>

            <form action="{{ isset($rule) ? route('organizations.sla-rules.update', [$organization, $rule]) : route('organizations.sla-rules.store', $organization) }}" method="POST">
                @csrf
                @if(isset($rule))
                    @method('PUT')
                @endif

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $rule->name ?? '') }}" placeholder="e.g., Standard Support" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description', $rule->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Scope & Conditions</h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Leave blank to match all tickets. Specify values to narrow the scope.</p>

                        <div class="mb-3">
                            <label for="brand_id" class="form-label">Brand (Optional)</label>
                            <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id">
                                <option value="">-- Match All Brands --</option>
                                @foreach($organization->brands as $brand)
                                    <option value="{{ $brand->id }}" @selected(old('brand_id', $rule->brand_id ?? null) == $brand->id)>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="location_id" class="form-label">Location (Optional)</label>
                            <select class="form-select @error('location_id') is-invalid @enderror" id="location_id" name="location_id">
                                <option value="">-- Match All Locations --</option>
                                @foreach($organization->locations as $location)
                                    <option value="{{ $location->id }}" @selected(old('location_id', $rule->location_id ?? null) == $location->id)>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category (Optional)</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                <option value="">-- Match All Categories --</option>
                                @foreach($organization->ticketCategories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $rule->category_id ?? null) == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority (Optional)</label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                <option value="">-- Match All Priorities --</option>
                                <option value="low" @selected(old('priority', $rule->priority ?? '') == 'low')>Low</option>
                                <option value="medium" @selected(old('priority', $rule->priority ?? '') == 'medium')>Medium</option>
                                <option value="high" @selected(old('priority', $rule->priority ?? '') == 'high')>High</option>
                                <option value="urgent" @selected(old('priority', $rule->priority ?? '') == 'urgent')>Urgent</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Response Times (Minutes)</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle"></i> Set response/resolution time targets. Leave blank to not track this metric.
                        </div>

                        <div class="mb-3">
                            <label for="first_response_time" class="form-label">First Response Time (Minutes)</label>
                            <input type="number" class="form-control @error('first_response_time') is-invalid @enderror" id="first_response_time" name="first_response_time" value="{{ old('first_response_time', $rule->first_response_time ?? '') }}" min="1" placeholder="e.g., 120">
                            <small class="form-text text-muted">Time allowed before first staff response.</small>
                            @error('first_response_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="resolution_time" class="form-label">Resolution Time (Minutes)</label>
                            <input type="number" class="form-control @error('resolution_time') is-invalid @enderror" id="resolution_time" name="resolution_time" value="{{ old('resolution_time', $rule->resolution_time ?? '') }}" min="1" placeholder="e.g., 1440">
                            <small class="form-text text-muted">Time allowed to resolve the ticket.</small>
                            @error('resolution_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="re_open_response_time" class="form-label">Re-Open Response Time (Minutes)</label>
                            <input type="number" class="form-control @error('re_open_response_time') is-invalid @enderror" id="re_open_response_time" name="re_open_response_time" value="{{ old('re_open_response_time', $rule->re_open_response_time ?? '') }}" min="1" placeholder="e.g., 60">
                            <small class="form-text text-muted">Time allowed to respond if a resolved ticket is reopened.</small>
                            @error('re_open_response_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" @checked(old('is_active', $rule->is_active ?? true))>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> {{ isset($rule) ? 'Update' : 'Create' }} SLA Rule
                    </button>
                    <a href="{{ route('organizations.settings', $organization) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
