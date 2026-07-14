@extends('layouts.app-modern')

@section('title', 'Add New Organization')

@section('content')
    <style>
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .form-section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary-600);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .form-section-title i {
            font-size: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-label-required::after {
            content: " *";
            color: #ef4444;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: var(--primary-600);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background-color: #f8faff;
        }
        .form-control.is-invalid:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }
        .form-text {
            font-size: 0.85rem;
            color: var(--text-tertiary);
            margin-top: 0.5rem;
        }
        .back-button {
            margin-bottom: 1.5rem;
        }
        .progress-steps {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .step.active .step-number {
            background: var(--primary-600);
            color: white;
        }
        .separator {
            flex: 1;
            height: 2px;
            background: #e5e7eb;
            margin: 0 -0.5rem;
            margin-top: 1rem;
        }
        .row > .col-md-6:not(:last-child) {
            padding-right: 0.75rem;
        }
        .row > .col-md-6:not(:first-child) {
            padding-left: 0.75rem;
        }
        .input-group-text {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
        }
        .form-footer {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f3f4f6;
        }
        .btn-primary {
            flex: 1;
            padding: 0.75rem;
            font-weight: 600;
        }
        .btn-outline-secondary {
            flex: 1;
            padding: 0.75rem;
            font-weight: 600;
        }
        .info-box {
            background: linear-gradient(135deg, #e0e7ff, #ede9fe);
            border-left: 4px solid var(--primary-600);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1.5rem;
            color: var(--primary-700);
            font-size: 0.9rem;
        }
        .info-box strong {
            display: block;
            margin-bottom: 0.5rem;
        }
    </style>

    <div class="back-button">
        <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="page-header mb-3">
        <div class="page-header-info">
            <h1>✨ Create New Organization</h1>
            <p>Create a new organization and assign an administrator to it</p>
        </div>
    </div>

    @if($errors->any())
        <x-alert-modern type="danger" dismissible>
            <strong>⚠️ Error in input data:</strong>
            <ul class="mb-0" style="padding-left: 1.5rem; margin-top: 0.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert-modern>
    @endif

    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="step active">
            <div class="step-number">1</div>
            <span>Organization Information</span>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <span>Administrator Information</span>
        </div>
    </div>

    <form action="{{ route('admin.organizations.store') }}" method="POST" id="createOrgForm">
        @csrf

        <div style="max-width: 800px;">
            <!-- Organization Information Section -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-building"></i>
                    Organization Information
                </div>

                <div class="form-group">
                    <label for="name" class="form-label form-label-required">Organization Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}"
                           placeholder="Example: Golden Hotel, Nile Hospital, Cairo University"
                           required autocomplete="off">
                    <p class="form-text">The official name of the organization - appears on all system pages</p>
                    @error('name')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4"
                              placeholder="Detailed description of the organization, type of business, services provided">{{ old('description') }}</textarea>
                    <p class="form-text">A short description to help identify the organization and its nature of work</p>
                    @error('description')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                   id="city" name="city" value="{{ old('city') }}"
                                   placeholder="Cairo">
                            @error('city')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror"
                                   id="country" name="country" value="{{ old('country') }}"
                                   placeholder="Egypt">
                            @error('country')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="max_channels" class="form-label form-label-required">Allowed Number of Channels</label>
                    <div class="input-group">
                        <input type="number" class="form-control @error('max_channels') is-invalid @enderror"
                               id="max_channels" name="max_channels" value="{{ old('max_channels', 4) }}"
                               min="1" max="1000" required>
                        <span class="input-group-text">channels</span>
                    </div>
                    <p class="form-text">The maximum number of channels the organization can create (can be modified later)</p>
                    @error('max_channels')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Owner/Admin Information Section -->
            <div class="form-section">
                <div class="form-section-title">
                    <i class="bi bi-person-badge"></i>
                    Organization Administrator Information
                </div>

                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    <i class="bi bi-info-circle"></i> An administrator account will be created with this information and can manage the organization
                </p>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="owner_first_name" class="form-label form-label-required">First Name</label>
                            <input type="text" class="form-control @error('owner_first_name') is-invalid @enderror"
                                   id="owner_first_name" name="owner_first_name" value="{{ old('owner_first_name') }}"
                                   placeholder="Ahmed" required autocomplete="given-name">
                            @error('owner_first_name')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="owner_last_name" class="form-label form-label-required">Last Name</label>
                            <input type="text" class="form-control @error('owner_last_name') is-invalid @enderror"
                                   id="owner_last_name" name="owner_last_name" value="{{ old('owner_last_name') }}"
                                   placeholder="Muhammad" required autocomplete="family-name">
                            @error('owner_last_name')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="owner_email" class="form-label form-label-required">Email</label>
                    <input type="email" class="form-control @error('owner_email') is-invalid @enderror"
                           id="owner_email" name="owner_email" value="{{ old('owner_email') }}"
                           placeholder="admin@organization.com" required autocomplete="email">
                    <p class="form-text">This email will be used to log in to the system</p>
                    @error('owner_email')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="owner_password" class="form-label form-label-required">Password</label>
                            <input type="password" class="form-control @error('owner_password') is-invalid @enderror"
                                   id="owner_password" name="owner_password" placeholder="••••••••"
                                   required minlength="8" autocomplete="new-password">
                            <p class="form-text">
                                <i class="bi bi-shield-check"></i>
                                Must be at least 8 characters
                            </p>
                            @error('owner_password')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="owner_password_confirmation" class="form-label form-label-required">Confirm Password</label>
                            <input type="password" class="form-control @error('owner_password_confirmation') is-invalid @enderror"
                                   id="owner_password_confirmation" name="owner_password_confirmation" placeholder="••••••••"
                                   required minlength="8" autocomplete="new-password">
                            <p class="form-text">Re-enter the same password</p>
                            @error('owner_password_confirmation')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <strong><i class="bi bi-lightbulb"></i> Important Note</strong>
                After creating the organization, the organization administrator will be able to log in and use all administrative system features.
            </div>

            <!-- Action Buttons -->
            <div class="form-footer">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle"></i> Create Organization
                </button>
                <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </div>
    </form>
@endsection
