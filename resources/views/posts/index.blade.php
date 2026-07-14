@extends('layouts.app-modern')

@section('content')
<style>
    .posts-header {
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

    .posts-header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
    .posts-header p { margin: 0; opacity: 0.9; font-size: 0.875rem; }

    .btn-new-post {
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
        transition: all 0.2s ease;
    }

    .btn-new-post:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); color: var(--primary-700); }

    .post-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }

    .post-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.06); border-color: var(--primary-200); }

    .post-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.5rem; }
    .post-card-title { font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem; }
    .post-card-meta { font-size: 0.8rem; color: var(--text-tertiary); display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }

    .status-badge {
        padding: 0.3rem 0.75rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
    }

    .status-badge.published { background-color: var(--success-50); color: var(--success-700); }
    .status-badge.pending_approval { background-color: var(--warning-50); color: var(--warning-700); }
    .status-badge.draft { background-color: var(--neutral-100); color: var(--neutral-700); }
    .status-badge.rejected { background-color: var(--danger-50); color: var(--danger-700); }
    .status-badge.archived { background-color: var(--neutral-100); color: var(--neutral-600); }
    .status-badge.scheduled { background-color: var(--info-50); color: var(--info-700); }

    .post-card-body { color: var(--text-secondary); font-size: 0.875rem; line-height: 1.5; margin-bottom: 1rem; }

    .post-card-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

    .post-action-btn {
        padding: 0.4rem 0.9rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border: 1px solid var(--surface-border);
        background: none;
        cursor: pointer;
        color: var(--text-secondary);
        transition: all 0.15s ease;
    }

    .post-action-btn:hover { background-color: var(--surface-hover); color: var(--text-primary); }
    .post-action-btn.view { color: var(--primary-600); border-color: var(--primary-200); }
    .post-action-btn.edit { color: var(--text-secondary); }
    .post-action-btn.approve { color: var(--success-600); border-color: var(--success-200); }
    .post-action-btn.reject { color: var(--danger-600); border-color: var(--danger-200); }

    .post-card-audience {
        font-size: 0.75rem;
        color: var(--text-tertiary);
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--surface-border);
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .empty-posts {
        text-align: center;
        padding: 4rem 1.5rem;
        background-color: var(--surface-bg);
        border: 1px dashed var(--surface-border);
        border-radius: 14px;
        color: var(--text-secondary);
    }

    @media (max-width: 576px) {
        .posts-header { flex-direction: column; align-items: flex-start; }
        .post-card-top { flex-direction: column; }
    }
</style>

<div class="container-lg py-4">
    <div class="posts-header">
        <div>
            <h1>Posts & Communications</h1>
            <p>Announcements, news, and updates for your organization</p>
        </div>
        @can('create', App\Models\Post::class)
            <a href="{{ route('posts.create') }}" class="btn-new-post">
                <i class="bi bi-plus-lg"></i> New Post
            </a>
        @endcan
    </div>

    @if($posts->isEmpty())
        <div class="empty-posts">
            <i class="bi bi-file-earmark-text" style="font-size: 2.5rem; opacity: 0.4; display: block; margin-bottom: 1rem;"></i>
            <p class="mb-0">No posts yet. Create one to get started.</p>
        </div>
    @else
        @foreach($posts as $post)
            <div class="post-card">
                <div class="post-card-top">
                    <div>
                        <div class="post-card-title">{{ $post->title ?? 'Untitled' }}</div>
                        <div class="post-card-meta">
                            <span><i class="bi bi-person"></i> {{ $post->author->full_name }}</span>
                            <span>&middot;</span>
                            <span><i class="bi bi-calendar"></i> {{ $post->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <span class="status-badge {{ $post->status }}">{{ str_replace('_', ' ', $post->status) }}</span>
                </div>

                <p class="post-card-body">{{ Str::limit($post->body, 150) }}</p>

                <div class="post-card-actions">
                    @can('view', $post)
                        <a href="{{ route('posts.show', $post) }}" class="post-action-btn view">
                            <i class="bi bi-eye"></i> View
                        </a>
                    @endcan

                    @can('update', $post)
                        <a href="{{ route('posts.edit', $post) }}" class="post-action-btn edit">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    @endcan

                    @if($post->status === 'pending_approval' && auth()->user()->can('approvePost', $post))
                        <form action="{{ route('posts.approve', $post) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="post-action-btn approve">
                                <i class="bi bi-check-circle"></i> Approve
                            </button>
                        </form>
                        <form action="{{ route('posts.reject', $post) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="post-action-btn reject">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                        </form>
                    @endif
                </div>

                @if($post->audience)
                    <div class="post-card-audience">
                        <i class="bi bi-people"></i> Audience: {{ $post->audience->segment->name ?? 'Custom rules' }}
                    </div>
                @endif
            </div>
        @endforeach

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
