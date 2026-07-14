@extends('layouts.app-modern')

@section('content')
<style>
    .brand-hero {
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

    .brand-hero-info { display: flex; align-items: center; gap: 1rem; }

    .brand-hero-avatar {
        width: 64px; height: 64px; border-radius: 16px;
        background: rgba(255,255,255,0.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.75rem; font-weight: 700;
    }

    .brand-hero h1 { font-size: 1.4rem; font-weight: 700; margin-bottom: 0.15rem; }
    .brand-hero p { margin: 0; opacity: 0.9; font-size: 0.85rem; }

    .brand-hero-actions { display: flex; gap: 0.5rem; }

    .btn-hero {
        padding: 0.55rem 1.1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.8rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: none;
        cursor: pointer;
    }

    .btn-hero.edit { background-color: rgba(255,255,255,0.2); color: white; }
    .btn-hero.delete { background-color: rgba(220,38,38,0.25); color: white; }

    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }

    .stat-box {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 14px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .stat-box-icon {
        width: 44px; height: 44px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }

    .stat-box-value { font-size: 1.3rem; font-weight: 700; color: var(--text-primary); }
    .stat-box-label { font-size: 0.75rem; color: var(--text-tertiary); }

    .section-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 14px;
        overflow: hidden;
    }

    .section-card-header {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-card-header h5 { margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-primary); }

    .channel-row {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
    }

    .channel-row:last-child { border-bottom: none; }

    .channel-row-icon {
        width: 38px; height: 38px; border-radius: 10px;
        background: var(--primary-50); color: var(--primary-600);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .channel-row-name { font-weight: 600; color: var(--text-primary); font-size: 0.875rem; }
    .channel-row-meta { font-size: 0.75rem; color: var(--text-tertiary); }

    .type-pill { font-size: 0.65rem; font-weight: 600; padding: 2px 8px; border-radius: 999px; text-transform: uppercase; }
    .type-pill.public { background-color: var(--primary-50); color: var(--primary-700); }
    .type-pill.private { background-color: var(--warning-50); color: var(--warning-700); }

    .empty-row { padding: 2rem 1.25rem; text-align: center; color: var(--text-tertiary); font-size: 0.85rem; }
</style>

<div class="container-lg py-4">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('organizations.brands.index', $organization) }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left"></i> Back to Brands
        </a>
    </div>

    <div class="brand-hero">
        <div class="brand-hero-info">
            <div class="brand-hero-avatar">{{ substr($brand->name, 0, 1) }}</div>
            <div>
                <h1>{{ $brand->name }}</h1>
                <p>{{ $brand->description ?? 'No description' }}</p>
            </div>
        </div>
        <div class="brand-hero-actions">
            <a href="{{ route('organizations.brands.edit', [$organization, $brand]) }}" class="btn-hero edit">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <form action="{{ route('organizations.brands.destroy', [$organization, $brand]) }}" method="POST" onsubmit="return confirm('Delete this brand? Its channels will be unassigned, not deleted.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-hero delete">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-box-icon" style="background-color: var(--primary-50); color: var(--primary-600);"><i class="bi bi-chat-dots"></i></div>
            <div>
                <div class="stat-box-value">{{ $channels->count() }}</div>
                <div class="stat-box-label">Channels</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-box-icon" style="background-color: var(--success-50); color: var(--success-600);"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-box-value">{{ $brand->followers_count }}</div>
                <div class="stat-box-label">Followers</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-box-icon" style="background-color: var(--warning-50); color: var(--warning-600);"><i class="bi bi-newspaper"></i></div>
            <div>
                <div class="stat-box-value">{{ $postsCount }}</div>
                <div class="stat-box-label">Posts</div>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-box-icon" style="background-color: {{ $brand->is_active ? 'var(--success-50)' : 'var(--neutral-100)' }}; color: {{ $brand->is_active ? 'var(--success-600)' : 'var(--neutral-500)' }};"><i class="bi bi-toggle-on"></i></div>
            <div>
                <div class="stat-box-value">{{ $brand->is_active ? 'Active' : 'Inactive' }}</div>
                <div class="stat-box-label">Status</div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="section-card-header">
            <h5><i class="bi bi-chat-dots"></i> Channels</h5>
            <a href="{{ route('tenant.channels.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg"></i> New Channel
            </a>
        </div>

        @forelse($channels as $channel)
            <div class="channel-row">
                <div class="channel-row-icon"><i class="bi bi-chat-dots"></i></div>
                <div style="flex: 1;">
                    <div class="channel-row-name">{{ $channel->name }}</div>
                    <div class="channel-row-meta">{{ $channel->users_count }} members · {{ $channel->posts_count }} posts</div>
                </div>
                <span class="type-pill {{ $channel->type }}">{{ $channel->type }}</span>
                <a href="{{ route('tenant.channels.show', $channel) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        @empty
            <div class="empty-row">No channels under this brand yet.</div>
        @endforelse
    </div>
</div>
@endsection
