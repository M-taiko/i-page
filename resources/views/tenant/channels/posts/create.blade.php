@extends('layouts.app-modern')

@section('title', 'Create Post - ' . $channel->name)

@section('content')
<style>
    .form-container {
        background: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: var(--space-6);
        max-width: 800px;
        margin: 0 auto;
    }

    .form-group {
        margin-bottom: var(--space-6);
    }

    .form-label {
        display: block;
        font-size: var(--text-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin-bottom: var(--space-2);
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: var(--space-3) var(--space-4);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
        font-family: inherit;
        background-color: var(--surface-bg);
        color: var(--text-primary);
        transition: all var(--transition-fast);
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary-500);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-textarea {
        min-height: 300px;
        resize: vertical;
    }

    .char-count {
        font-size: var(--text-xs);
        color: var(--text-tertiary);
        margin-top: var(--space-1);
    }

    .form-actions {
        display: flex;
        gap: var(--space-3);
        margin-top: var(--space-6);
    }

    .btn {
        padding: var(--space-3) var(--space-6);
        border: none;
        border-radius: var(--radius-md);
        font-size: var(--text-sm);
        font-weight: var(--font-weight-medium);
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--space-2);
        transition: all var(--transition-fast);
    }

    .btn-primary {
        background-color: var(--primary-600);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--primary-700);
    }

    .btn-secondary {
        background-color: var(--surface-hover);
        color: var(--text-primary);
        border: 1px solid var(--surface-border);
    }

    .btn-secondary:hover {
        background-color: var(--primary-50);
    }

    .header-section {
        margin-bottom: var(--space-6);
    }

    .header-title {
        font-size: var(--text-2xl);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
        margin: 0 0 var(--space-2) 0;
    }

    .header-subtitle {
        color: var(--text-secondary);
        margin: 0;
    }

    .channel-info {
        background: var(--primary-50);
        border: 1px solid var(--primary-200);
        border-radius: var(--radius-md);
        padding: var(--space-3) var(--space-4);
        margin-bottom: var(--space-6);
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .channel-avatar {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        flex-shrink: 0;
    }

    .channel-details h4 {
        margin: 0;
        font-size: var(--text-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
    }

    .channel-details small {
        color: var(--text-secondary);
        font-size: var(--text-xs);
    }

    .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
        width: 100%;
    }

    .file-input-label {
        display: block;
        padding: var(--space-6);
        border: 2px dashed var(--surface-border);
        border-radius: var(--radius-md);
        background: var(--surface-hover);
        text-align: center;
        cursor: pointer;
        transition: all var(--transition-fast);
    }

    .file-input-label:hover {
        border-color: var(--primary-500);
        background: var(--primary-50);
    }

    input[type="file"] {
        display: none;
    }

    .file-name {
        margin-top: var(--space-2);
        font-size: var(--text-sm);
        color: var(--primary-600);
    }

    .error-message {
        color: var(--danger-600);
        font-size: var(--text-sm);
        margin-top: var(--space-1);
    }
</style>

<!-- Header -->
<div class="header-section">
    <h1 class="header-title">
        <i class="bi bi-pencil-square"></i> Create Post
    </h1>
    <p class="header-subtitle">Add a new post to {{ $channel->name }}</p>
</div>

<!-- Channel Info -->
<div class="channel-info">
    <div class="channel-avatar">
        <i class="bi bi-chat-dots"></i>
    </div>
    <div class="channel-details">
        <h4>{{ $channel->name }}</h4>
        <small>{{ $channel->description ?? 'No description' }}</small>
    </div>
</div>

<!-- Form -->
<form method="POST" action="{{ route('tenant.channels.posts.store', $channel->id) }}" enctype="multipart/form-data">
    @csrf

    <div class="form-container">
        <!-- Body -->
        <div class="form-group">
            <label for="body" class="form-label">
                <i class="bi bi-chat-left"></i> Post Content
            </label>
            <textarea id="body" name="body" class="form-textarea @error('body') border-danger @enderror"
                      placeholder="Write your post content here (minimum 10 characters)..." required>{{ old('body') }}</textarea>
            <div class="char-count">
                <span id="charCount">0</span> / 5000 characters
            </div>
            @error('body')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Image -->
        <div class="form-group">
            <label for="image" class="form-label">
                <i class="bi bi-image"></i> Post Image (Optional)
            </label>
            <div class="file-input-wrapper">
                <label for="image" class="file-input-label">
                    <div>
                        <i class="bi bi-cloud-upload" style="font-size: 2rem; color: var(--primary-600);"></i>
                        <p style="margin: var(--space-2) 0 0 0; color: var(--text-secondary);">
                            Click to upload or drag and drop
                        </p>
                        <small style="color: var(--text-tertiary);">
                            PNG, JPG, GIF up to 2MB
                        </small>
                    </div>
                </label>
                <input type="file" id="image" name="image" accept="image/*">
                <div id="fileName" class="file-name"></div>
            </div>
            @error('image')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Publish Post
            </button>
            <a href="{{ route('tenant.channels.show', $channel->id) }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancel
            </a>
        </div>
    </div>
</form>

<script>
    // Character counter
    const bodyInput = document.getElementById('body');
    const charCount = document.getElementById('charCount');

    bodyInput.addEventListener('input', () => {
        charCount.textContent = bodyInput.value.length;
    });

    // File input handler
    const fileInput = document.getElementById('image');
    const fileLabel = document.querySelector('.file-input-label');
    const fileName = document.getElementById('fileName');

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            fileName.textContent = '✓ ' + fileInput.files[0].name + ' selected';
        } else {
            fileName.textContent = '';
        }
    });

    fileLabel.addEventListener('dragover', (e) => {
        e.preventDefault();
        fileLabel.style.borderColor = 'var(--primary-500)';
        fileLabel.style.background = 'var(--primary-50)';
    });

    fileLabel.addEventListener('dragleave', () => {
        fileLabel.style.borderColor = 'var(--surface-border)';
        fileLabel.style.background = 'var(--surface-hover)';
    });

    fileLabel.addEventListener('drop', (e) => {
        e.preventDefault();
        fileLabel.style.borderColor = 'var(--surface-border)';
        fileLabel.style.background = 'var(--surface-hover)';

        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            fileName.textContent = '✓ ' + e.dataTransfer.files[0].name + ' selected';
        }
    });
</script>

@endsection
