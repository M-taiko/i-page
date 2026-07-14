@extends('layouts.app-modern')

@section('content')
<style>
    .post-show-header {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: 2rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .post-show-header h1 { font-size: 1.4rem; font-weight: 700; margin-bottom: 0.4rem; }
    .post-show-header .meta { margin: 0; opacity: 0.9; font-size: 0.85rem; }

    .post-show-badges { display: flex; gap: 0.5rem; flex-wrap: wrap; }

    .badge-pill {
        padding: 0.35rem 0.8rem;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        background-color: rgba(255,255,255,0.2);
        color: white;
        white-space: nowrap;
    }

    .section-card {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 14px;
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .section-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--surface-border);
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-card-body { padding: 1.25rem; }

    .post-summary { color: var(--text-secondary); font-size: 0.95rem; font-style: italic; margin-bottom: 1rem; }
    .post-body-text { color: var(--text-primary); font-size: 0.9rem; line-height: 1.7; white-space: pre-line; }

    .attachments-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--surface-border); }
    .attachments-grid img { width: 100%; height: 90px; object-fit: cover; border-radius: 10px; }
    .attachment-file { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.3rem; padding: 1rem 0.5rem; border: 1px solid var(--surface-border); border-radius: 10px; text-decoration: none; color: var(--primary-600); font-size: 0.7rem; text-align: center; }

    .engagement-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
    .engagement-stat { text-align: center; }
    .engagement-value { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
    .engagement-label { font-size: 0.75rem; color: var(--text-tertiary); text-transform: uppercase; letter-spacing: 0.4px; margin-top: 0.2rem; }
    .engagement-pct { font-size: 0.7rem; color: var(--primary-600); font-weight: 600; margin-top: 0.15rem; }

    .detail-row { display: flex; justify-content: space-between; padding: 0.6rem 0; border-bottom: 1px solid var(--surface-border); font-size: 0.825rem; }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: var(--text-tertiary); }
    .detail-value { color: var(--text-primary); font-weight: 600; text-align: right; }

    .action-btn-full {
        width: 100%;
        padding: 0.6rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.825rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        border: none;
        cursor: pointer;
        margin-bottom: 0.5rem;
    }

    .action-btn-full:last-child { margin-bottom: 0; }
    .action-btn-full.primary { background-color: var(--primary-600); color: white; }
    .action-btn-full.outline-danger { background-color: transparent; border: 1px solid var(--danger-300); color: var(--danger-600); }
    .action-btn-full.success { background-color: var(--success-600); color: white; }
    .action-btn-full.danger { background-color: var(--danger-600); color: white; }

    .pending-note { font-size: 0.8rem; color: var(--text-tertiary); text-align: center; padding: 0.5rem 0; }

    @media (max-width: 640px) {
        .engagement-grid { grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
    }
</style>

<div class="container-lg py-4">
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('posts.index') }}" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left"></i> Back to Posts
        </a>
    </div>

    <div class="post-show-header">
        <div>
            <h1>{{ $post->title }}</h1>
            <p class="meta">
                <i class="bi bi-person"></i> {{ $post->author->full_name ?? 'System' }}
                &middot; <i class="bi bi-calendar"></i> {{ $post->created_at->diffForHumans() }}
            </p>
        </div>
        <div class="post-show-badges">
            <span class="badge-pill">{{ str_replace('_', ' ', $post->status) }}</span>
            <span class="badge-pill">{{ $post->priority }}</span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="section-card">
                @if($post->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" alt="" style="width: 100%; max-height: 420px; object-fit: cover; display: block;">
                @endif
                <div class="section-card-body">
                    @if($post->summary)
                        <p class="post-summary">{{ $post->summary }}</p>
                    @endif

                    <div class="post-body-text">{{ $post->body }}</div>

                    @if($post->media()->count())
                        <div class="attachments-grid">
                            @foreach($post->media as $media)
                                @if($media->isImage())
                                    <img src="{{ $media->getUrl() }}" alt="Attachment">
                                @else
                                    <a href="{{ $media->getUrl() }}" class="attachment-file">
                                        <i class="bi bi-file-earmark-pdf" style="font-size: 1.3rem;"></i>
                                        {{ Str::limit($media->file_name, 16) }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if($post->status === 'published' || $post->status === 'scheduled')
                @php($stats = $post->getStats())
                <div class="section-card">
                    <div class="section-card-header"><i class="bi bi-graph-up"></i> Engagement</div>
                    <div class="section-card-body">
                        <div class="engagement-grid">
                            <div class="engagement-stat">
                                <div class="engagement-value">{{ $stats['delivered'] }}</div>
                                <div class="engagement-label">Delivered</div>
                                @if($stats['total_recipients'])
                                    <div class="engagement-pct">{{ round($stats['delivered'] / $stats['total_recipients'] * 100) }}%</div>
                                @endif
                            </div>
                            <div class="engagement-stat">
                                <div class="engagement-value">{{ $stats['viewed'] }}</div>
                                <div class="engagement-label">Viewed</div>
                                @if($stats['total_recipients'])
                                    <div class="engagement-pct">{{ round($stats['viewed'] / $stats['total_recipients'] * 100) }}%</div>
                                @endif
                            </div>
                            <div class="engagement-stat">
                                <div class="engagement-value">{{ $stats['read'] }}</div>
                                <div class="engagement-label">Read</div>
                                @if($stats['total_recipients'])
                                    <div class="engagement-pct">{{ round($stats['read'] / $stats['total_recipients'] * 100) }}%</div>
                                @endif
                            </div>
                            <div class="engagement-stat">
                                <div class="engagement-value">{{ $stats['acknowledged'] }}</div>
                                <div class="engagement-label">Acknowledged</div>
                                @if($stats['total_recipients'])
                                    <div class="engagement-pct">{{ round($stats['acknowledged'] / $stats['total_recipients'] * 100) }}%</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="section-card">
                <div class="section-card-header"><i class="bi bi-info-circle"></i> Details</div>
                <div class="section-card-body" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                    <div class="detail-row">
                        <span class="detail-label">Type</span>
                        <span class="detail-value">{{ str_replace('_', ' ', ucfirst($post->post_type)) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Language</span>
                        <span class="detail-value">{{ $post->language === 'ar' ? 'العربية' : 'English' }}</span>
                    </div>
                    @if($post->scheduled_for)
                        <div class="detail-row">
                            <span class="detail-label">Scheduled For</span>
                            <span class="detail-value">{{ $post->scheduled_for->format('M d, Y H:i') }}</span>
                        </div>
                    @endif
                    @if($post->published_at)
                        <div class="detail-row">
                            <span class="detail-label">Published</span>
                            <span class="detail-value">{{ $post->published_at->format('M d, Y H:i') }}</span>
                        </div>
                    @endif
                    @if($post->approved_by)
                        <div class="detail-row">
                            <span class="detail-label">Approved By</span>
                            <span class="detail-value">{{ $post->approver->full_name ?? 'Admin' }}</span>
                        </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Needs Acknowledgment</span>
                        <span class="detail-value">{{ $post->requires_acknowledgment ? 'Yes' : 'No' }}</span>
                    </div>
                    @if($post->brand)
                        <div class="detail-row">
                            <span class="detail-label">Brand</span>
                            <span class="detail-value">{{ $post->brand->name }}</span>
                        </div>
                    @endif
                    @if($post->location)
                        <div class="detail-row">
                            <span class="detail-label">Location</span>
                            <span class="detail-value">{{ $post->location->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            @can('update', $post)
                @if($post->status === 'draft' || $post->status === 'pending_approval')
                    <div class="section-card">
                        <div class="section-card-header"><i class="bi bi-sliders"></i> Actions</div>
                        <div class="section-card-body">
                            @if($post->status === 'draft')
                                <a href="{{ route('posts.edit', $post) }}" class="action-btn-full primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn-full outline-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            @elseif($post->status === 'pending_approval')
                                <p class="pending-note">Awaiting approval from administrator</p>
                            @endif
                        </div>
                    </div>
                @endif
            @endcan

            @can('approve', $post)
                @if($post->status === 'pending_approval')
                    <div class="section-card">
                        <div class="section-card-header"><i class="bi bi-shield-check"></i> Admin Actions</div>
                        <div class="section-card-body">
                            <form action="{{ route('posts.approve', $post) }}" method="POST">
                                @csrf
                                <button class="action-btn-full success" type="submit">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                            </form>
                            <form action="{{ route('posts.reject', $post) }}" method="POST">
                                @csrf
                                <button class="action-btn-full danger" type="submit">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</div>
@endsection
