@extends('layouts.app-modern')

@section('title', $channel->name)

@section('content')
    <style>
        .info-group {
            margin-bottom: 1.5rem;
        }
        .info-label {
            font-size: 0.85rem;
            color: var(--text-tertiary);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }
        .info-value {
            font-size: 1rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--primary-50), #e0e7ff);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid var(--primary-100);
        }
        .stat-card-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-600);
        }
        .stat-card-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }
        .header-section {
            background: linear-gradient(135deg, var(--primary-600), var(--primary-700));
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .header-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }
        .header-subtitle {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
        }
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(1, 1fr);
            }
            .header-title {
                font-size: 1.5rem;
            }
        }
    </style>

    <div class="page-header mb-2">
        <div class="page-header-top">
            <div class="page-header-info">
                <a href="{{ route('tenant.channels.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="bi bi-arrow-left"></i> Back to Channels
                </a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <div class="header-section">
        <h1 class="header-title">📺 {{ $channel->name }}</h1>
        <p class="header-subtitle">
            <span class="badge {{ $channel->type === 'public' ? 'bg-success' : 'bg-warning' }}">
                {{ $channel->type === 'public' ? '🌍 Public' : '🔒 Private' }}
            </span>
            <span class="badge bg-info ms-2">{{ ucfirst($channel->status) }}</span>
        </p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-value">{{ $stats['members'] }}</div>
            <div class="stat-card-label">Members</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-value">{{ $stats['posts'] }}</div>
            <div class="stat-card-label">Posts</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-value">{{ $stats['qr_codes'] }}</div>
            <div class="stat-card-label">QR Codes</div>
        </div>
    </div>

    <div class="row">
        <!-- Channel Information -->
        <div class="col-lg-8">
            <!-- Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">📋 Channel Information</h5>
                </div>
                <div class="card-body">
                    <div class="info-group">
                        <span class="info-label">Channel Name</span>
                        <span class="info-value">{{ $channel->name }}</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <span class="info-label">Channel Type</span>
                                @if($channel->type === 'public')
                                    <span class="badge bg-success">🌍 Public Channel</span>
                                @else
                                    <span class="badge bg-warning">🔒 Private Channel</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <span class="info-label">Status</span>
                                <span class="badge bg-info">{{ ucfirst($channel->status) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-group">
                        <span class="info-label">Description</span>
                        <span class="info-value">{{ $channel->description ?? 'No description provided' }}</span>
                    </div>
                </div>
            </div>

            <!-- Additional Details Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">ℹ️ Additional Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group">
                                <span class="info-label">Created Date</span>
                                <span class="info-value">{{ $channel->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group">
                                <span class="info-label">Created At</span>
                                <span class="info-value">{{ $channel->created_at->format('h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                    @if($channel->updated_at->ne($channel->created_at))
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <span class="info-label">Last Updated</span>
                                    <span class="info-value">{{ $channel->updated_at->format('M d, Y h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">⚙️ Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('tenant.channels.posts.create', $channel->id) }}" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Create Post
                        </a>
                        <a href="{{ route('tenant.channels.edit', $channel->id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Edit Channel
                        </a>
                        <form action="{{ route('tenant.channels.destroy', $channel->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('⚠️ Are you sure you want to delete this channel?\nAll associated data will be deleted!')">
                                <i class="bi bi-trash"></i> Delete Channel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Members Section -->
    <div style="margin-top: var(--space-8);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <h2 style="font-size: var(--text-lg); font-weight: var(--font-weight-bold); margin: 0; color: var(--text-primary);">
                <i class="bi bi-people"></i> Members ({{ $members->count() }})
            </h2>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                <i class="bi bi-person-plus"></i> Invite Member
            </button>
        </div>

        @if($members->count() > 0)
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th style="width: 160px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                                <tr>
                                    <td>{{ $member->full_name }}</td>
                                    <td class="text-muted small">{{ $member->email }}</td>
                                    <td>
                                        <form action="{{ route('tenant.channels.members.update', [$channel, $member]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <select name="role" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                                <option value="member" @selected($member->pivot->role === 'member')>Staff</option>
                                                <option value="moderator" @selected($member->pivot->role === 'moderator')>Moderator</option>
                                                <option value="admin" @selected($member->pivot->role === 'admin')>Manager</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-muted small">{{ $member->pivot->joined_at ? \Illuminate\Support\Carbon::parse($member->pivot->joined_at)->format('M d, Y') : '—' }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('tenant.channels.members.destroy', [$channel, $member]) }}" method="POST" onsubmit="return confirm('Remove this member from the channel?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-lg"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div style="background: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); padding: var(--space-6); text-align: center;">
                <p style="color: var(--text-secondary); margin: 0;">No members yet. Invite someone to get started.</p>
            </div>
        @endif
    </div>

    <!-- Invite Member Modal -->
    <div class="modal fade" id="inviteMemberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tenant.channels.members.store', $channel) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-plus"></i> Invite Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Enter the email of any user — if they don't have an account yet, one will be created for them automatically.</p>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" placeholder="Optional, if new user">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" placeholder="Optional, if new user">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="member">Staff — can post</option>
                                <option value="moderator">Moderator — moderates content</option>
                                <option value="admin">Manager — full channel control</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Add to Channel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Posts Section -->
    <div style="margin-top: var(--space-8);">
        <h2 style="font-size: var(--text-lg); font-weight: var(--font-weight-bold); margin-bottom: var(--space-4); color: var(--text-primary);">
            <i class="bi bi-newspaper"></i> Posts in this Channel
        </h2>

        @if($channel->posts()->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--space-4);">
                @foreach($channel->posts()->latest('published_at')->get() as $post)
                    <div style="background: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); padding: var(--space-4);">
                        <div style="display: flex; align-items: center; gap: var(--space-3); margin-bottom: var(--space-3);">
                            <div style="width: 40px; height: 40px; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: var(--text-sm);">
                                {{ $post->author->initials }}
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin: 0; font-size: var(--text-sm); font-weight: var(--font-weight-semibold); color: var(--text-primary);">
                                    {{ $post->author->full_name }}
                                </h4>
                                <small style="color: var(--text-tertiary);">
                                    {{ $post->published_at?->diffForHumans() ?? 'Not published' }}
                                </small>
                            </div>
                        </div>

                            <p style="margin: 0 0 var(--space-3); color: var(--text-secondary); font-size: var(--text-sm); line-height: var(--line-height-relaxed);">
                            {{ Str::limit($post->body, 200) }}
                        </p>

                        <div style="display: flex; gap: var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary);">
                            <span><i class="bi bi-hand-thumbs-up"></i> Likes</span>
                            <span><i class="bi bi-chat"></i> Comments</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="background: var(--surface-bg); border: 1px solid var(--surface-border); border-radius: var(--radius-lg); padding: var(--space-6); text-align: center;">
                <div style="font-size: 2rem; margin-bottom: var(--space-3);">📭</div>
                <h4 style="color: var(--text-primary); margin: 0 0 var(--space-2) 0;">No Posts Yet</h4>
                <p style="color: var(--text-secondary); margin: 0 0 var(--space-4) 0;">Start by creating the first post in this channel</p>
                <a href="{{ route('tenant.channels.posts.create', $channel->id) }}" class="btn btn-success" style="text-decoration: none; display: inline-flex; align-items: center; gap: var(--space-2);">
                    <i class="bi bi-plus-circle"></i> Create First Post
                </a>
            </div>
        @endif
    </div>
@endsection
