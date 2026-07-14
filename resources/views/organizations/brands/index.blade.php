@extends('layouts.app-modern')

@section('content')
<style>
    .brands-header {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: 2rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .brands-header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
    .brands-header p { margin: 0; opacity: 0.9; font-size: 0.875rem; }

    .btn-new-brand {
        background-color: white;
        color: var(--primary-700);
        border: none;
        padding: 0.6rem 1.25rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-new-brand:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); color: var(--primary-700); }

    .brands-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }

    .brand-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 14px;
        padding: 1.5rem;
        text-decoration: none;
        display: block;
        transition: all 0.2s ease;
    }

    .brand-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); border-color: var(--primary-200); transform: translateY(-1px); }

    .brand-card-top { display: flex; align-items: center; gap: 0.9rem; margin-bottom: 1rem; }

    .brand-avatar {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-weight: 700; font-size: 1.1rem; flex-shrink: 0;
    }

    .brand-name { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }

    .brand-status {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 999px;
        display: inline-block;
    }

    .brand-status.active { background-color: var(--success-50); color: var(--success-700); }
    .brand-status.inactive { background-color: var(--neutral-100); color: var(--neutral-600); }

    .brand-stats { display: flex; gap: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--surface-border); }
    .brand-stat { text-align: center; flex: 1; }
    .brand-stat-value { font-size: 1.1rem; font-weight: 700; color: var(--text-primary); }
    .brand-stat-label { font-size: 0.7rem; color: var(--text-tertiary); text-transform: uppercase; letter-spacing: 0.4px; }

    .empty-brands {
        text-align: center;
        padding: 4rem 1.5rem;
        background-color: var(--surface-bg);
        border: 1px dashed var(--surface-border);
        border-radius: 14px;
        color: var(--text-secondary);
    }
</style>

@php
    $avatarPalette = ['#4557f5', '#7c3aed', '#059669', '#d97706', '#dc2626', '#2563eb', '#db2777'];
    $colorFor = fn($seed) => $avatarPalette[crc32($seed) % count($avatarPalette)];
@endphp

<div class="container-lg py-4">
    <div class="brands-header">
        <div>
            <h1>Brands</h1>
            <p>Manage your organization's brands and their channels</p>
        </div>
        <a href="{{ route('organizations.brands.create', $organization) }}" class="btn-new-brand">
            <i class="bi bi-plus-lg"></i> New Brand
        </a>
    </div>

    @if($brands->isEmpty())
        <div class="empty-brands">
            <i class="bi bi-bookmark-star" style="font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 1rem;"></i>
            <p class="mb-0">No brands yet. Create one to start organizing your channels.</p>
        </div>
    @else
        <div class="brands-grid">
            @foreach($brands as $brand)
                <a href="{{ route('organizations.brands.show', [$organization, $brand]) }}" class="brand-card">
                    <div class="brand-card-top">
                        <div class="brand-avatar" style="background-color: {{ $colorFor($brand->name) }};">
                            {{ substr($brand->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="brand-name">{{ $brand->name }}</div>
                            <span class="brand-status {{ $brand->is_active ? 'active' : 'inactive' }}">
                                {{ $brand->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="brand-stats">
                        <div class="brand-stat">
                            <div class="brand-stat-value">{{ $brand->channels_count }}</div>
                            <div class="brand-stat-label">Channels</div>
                        </div>
                        <div class="brand-stat">
                            <div class="brand-stat-value">{{ $brand->followers_count }}</div>
                            <div class="brand-stat-label">Followers</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
