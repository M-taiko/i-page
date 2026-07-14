@extends('layouts.app-modern')

@section('title', 'Edit Channel')

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
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-600);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
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
        .form-check {
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }
        .form-check:hover {
            background: #f9fafb;
            border-color: var(--primary-600);
        }
        .form-check-input {
            width: 1.2rem;
            height: 1.2rem;
            margin-right: 0.75rem;
        }
        .form-check-label {
            margin-bottom: 0;
            cursor: pointer;
            font-weight: 500;
        }
        .back-button {
            margin-bottom: 1.5rem;
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
            color: var(--primary-700);
            font-size: 0.9rem;
        }
        .info-box strong {
            display: block;
            margin-bottom: 0.5rem;
        }
    </style>

    <div class="back-button">
        <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Channel
        </a>
    </div>

    <div class="page-header mb-3">
        <div class="page-header-info">
            <h1>✏️ Edit Channel</h1>
            <p>Update channel information and settings</p>
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

    <form action="{{ route('tenant.channels.update', $channel->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="max-width: 700px;">
            <!-- Channel Information Section -->
            <div class="form-section">
                <h3 class="form-section-title">📋 Channel Information</h3>

                <div class="form-group">
                    <label for="brand_id" class="form-label form-label-required">Brand</label>
                    <select class="form-control @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id" required>
                        <option value="">Select a brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $channel->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    <p class="form-text">Channels belong to a brand within your organization</p>
                    @error('brand_id')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="name" class="form-label form-label-required">Channel Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $channel->name) }}"
                           placeholder="Example: Reception, Support, Announcements"
                           required autocomplete="off">
                    <p class="form-text">The name that will appear for all members of this channel</p>
                    @error('name')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Channel Type Section -->
            <div class="form-section">
                <h3 class="form-section-title">🔒 Channel Type</h3>

                <div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="public" value="public" {{ old('type', $channel->type) === 'public' ? 'checked' : '' }}>
                        <label class="form-check-label" for="public">
                            <strong>🌍 Public Channel</strong>
                            <p style="margin: 0; font-size: 0.85rem; color: var(--text-tertiary);">
                                Anyone in your organization can join and see all posts
                            </p>
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="type" id="private" value="private" {{ old('type', $channel->type) === 'private' ? 'checked' : '' }}>
                        <label class="form-check-label" for="private">
                            <strong>🔒 Private Channel</strong>
                            <p style="margin: 0; font-size: 0.85rem; color: var(--text-tertiary);">
                                Only invited members can join and see posts
                            </p>
                        </label>
                    </div>
                </div>

                @error('type')<div class="text-danger small mt-2"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <strong><i class="bi bi-lightbulb"></i> Tip</strong>
                Use public channels for organization-wide announcements and private channels for specific teams or departments.
            </div>

            <!-- Action Buttons -->
            <div class="form-footer">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
                <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-outline-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </div>
    </form>

@endsection
