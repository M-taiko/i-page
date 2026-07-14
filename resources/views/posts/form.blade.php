@extends('layouts.app-modern')

@section('content')
<style>
    .form-page-header {
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--secondary-600) 100%);
        color: white;
        padding: 2rem 1.5rem;
        border-radius: 16px;
        margin-bottom: 1.5rem;
    }

    .form-page-header h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.25rem; }
    .form-page-header p { margin: 0; opacity: 0.9; font-size: 0.875rem; }

    .form-section {
        background-color: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: 14px;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
    }

    .form-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group { margin-bottom: 1.25rem; }
    .form-group:last-child { margin-bottom: 0; }

    .form-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.4rem;
    }

    .form-control-modern, .form-select-modern {
        width: 100%;
        padding: 0.65rem 0.9rem;
        border: 1px solid var(--surface-border);
        border-radius: 10px;
        font-size: 0.9rem;
        background-color: var(--surface-bg-secondary);
        color: var(--text-primary);
        transition: all 0.15s ease;
    }

    .form-control-modern:focus, .form-select-modern:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 3px rgba(69, 87, 245, 0.1);
        background-color: var(--surface-bg);
    }

    textarea.form-control-modern { resize: vertical; min-height: 140px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }

    .form-check-modern {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.75rem 1rem;
        background-color: var(--surface-bg-secondary);
        border-radius: 10px;
        margin-top: 1.6rem;
    }

    .form-check-modern input { width: 18px; height: 18px; cursor: pointer; }
    .form-check-modern label { margin: 0; cursor: pointer; font-weight: 500; color: var(--text-primary); font-size: 0.85rem; }

    .form-hint { font-size: 0.75rem; color: var(--text-tertiary); margin-top: 0.4rem; }

    .file-drop {
        border: 2px dashed var(--surface-border);
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        color: var(--text-tertiary);
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .file-drop:hover { border-color: var(--primary-400); color: var(--primary-600); background-color: var(--primary-50); }

    .form-actions { display: flex; gap: 0.75rem; }

    .btn-submit-modern {
        padding: 0.7rem 1.75rem;
        background-color: var(--primary-600);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.15s ease;
    }

    .btn-submit-modern:hover { background-color: var(--primary-700); transform: translateY(-1px); }

    .btn-cancel-modern {
        padding: 0.7rem 1.75rem;
        background-color: var(--surface-hover);
        color: var(--text-primary);
        border: 1px solid var(--surface-border);
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    @media (max-width: 640px) {
        .form-row { grid-template-columns: 1fr; }
    }
</style>

<div class="container-lg py-4" style="max-width: 760px;">
    <div class="form-page-header">
        <h1>{{ isset($post) ? 'Edit Post' : 'Create Post' }}</h1>
        <p>{{ isset($post) ? 'Update your announcement or update before it goes live' : 'Share an announcement, news update, or offer with your audience' }}</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($post))
            @method('PUT')
        @endif

        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-file-text"></i> Content</div>

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control-modern" id="title" name="title" value="{{ old('title', $post->title ?? '') }}" placeholder="Give your post a clear title" required>
            </div>

            <div class="form-group">
                <label for="summary">Summary <span style="font-weight:400;color:var(--text-tertiary);">(optional)</span></label>
                <input type="text" class="form-control-modern" id="summary" name="summary" value="{{ old('summary', $post->summary ?? '') }}" placeholder="A short one-line summary">
            </div>

            <div class="form-group">
                <label for="body">Content</label>
                <textarea class="form-control-modern" id="body" name="body" rows="6" placeholder="Write your announcement..." required>{{ old('body', $post->body ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label for="image">Cover Image <span style="font-weight:400;color:var(--text-tertiary);">(optional — shown at the top of the post)</span></label>

                @if(isset($post) && $post->image_path)
                    <div id="currentImagePreview" style="margin-bottom: 0.75rem; position: relative; display: inline-block;">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" alt="" style="max-height: 160px; border-radius: 10px; display: block;">
                        <label style="position: absolute; top: 6px; right: 6px; background: rgba(220,38,38,0.85); color: white; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <input type="checkbox" name="remove_image" value="1" style="display: none;" onchange="document.getElementById('currentImagePreview').style.opacity = this.checked ? 0.3 : 1;">
                            <i class="bi bi-x-lg" style="font-size: 0.75rem;"></i>
                        </label>
                    </div>
                @endif

                <label class="file-drop" for="image" id="imageDropLabel">
                    <i class="bi bi-image" style="font-size: 1.5rem; display: block; margin-bottom: 0.4rem;"></i>
                    <span id="imageFileName">Click to upload a cover image</span>
                </label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp" style="display: none;" onchange="document.getElementById('imageFileName').textContent = this.files[0] ? this.files[0].name : 'Click to upload a cover image'">
            </div>

            <div class="form-group">
                <label for="media">Attachments <span style="font-weight:400;color:var(--text-tertiary);">(optional)</span></label>
                <label class="file-drop" for="media">
                    <i class="bi bi-cloud-arrow-up" style="font-size: 1.5rem; display: block; margin-bottom: 0.4rem;"></i>
                    Click to upload images or PDFs
                </label>
                <input type="file" id="media" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf" style="display: none;" onchange="document.getElementById('mediaFileNames').textContent = [...this.files].map(f => f.name).join(', ')">
                <div id="mediaFileNames" class="form-hint"></div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-sliders"></i> Settings</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="post_type">Type</label>
                    <select class="form-select-modern" id="post_type" name="post_type">
                        <option value="announcement" @selected(old('post_type', $post->post_type ?? '') === 'announcement')>Announcement</option>
                        <option value="news" @selected(old('post_type', $post->post_type ?? '') === 'news')>News</option>
                        <option value="offer" @selected(old('post_type', $post->post_type ?? '') === 'offer')>Offer</option>
                        <option value="emergency" @selected(old('post_type', $post->post_type ?? '') === 'emergency')>Emergency</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select class="form-select-modern" id="priority" name="priority">
                        <option value="low" @selected(old('priority', $post->priority ?? '') === 'low')>Low</option>
                        <option value="medium" @selected(old('priority', $post->priority ?? '') === 'medium')>Medium</option>
                        <option value="high" @selected(old('priority', $post->priority ?? '') === 'high')>High</option>
                        <option value="critical" @selected(old('priority', $post->priority ?? '') === 'critical')>Critical</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="language">Language</label>
                    <select class="form-select-modern" id="language" name="language">
                        <option value="en" @selected(old('language', $post->language ?? '') === 'en')>English</option>
                        <option value="ar" @selected(old('language', $post->language ?? '') === 'ar')>Arabic</option>
                    </select>
                </div>

                <div class="form-check-modern">
                    <input type="checkbox" id="requires_acknowledgment" name="requires_acknowledgment" @checked(old('requires_acknowledgment', $post->requires_acknowledgment ?? false))>
                    <label for="requires_acknowledgment">Requires Acknowledgment</label>
                </div>
            </div>

            @if(!isset($post) || $post->status === 'draft')
                <div class="form-group">
                    <label for="status">Publish As</label>
                    <select class="form-select-modern" id="status" name="status">
                        <option value="draft">Draft</option>
                        <option value="pending_approval">Send for Approval</option>
                        <option value="published">Publish Immediately</option>
                    </select>
                    <div class="form-hint">Drafts are only visible to you. Sending for approval requires an admin review. Immediate publish is available to admins only.</div>
                </div>
            @endif
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit-modern">
                <i class="bi bi-check-lg"></i> {{ isset($post) ? 'Update Post' : 'Create Post' }}
            </button>
            <a href="{{ route('posts.index') }}" class="btn-cancel-modern">Cancel</a>
        </div>
    </form>
</div>
@endsection
