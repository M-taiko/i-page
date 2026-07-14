@props([
    'name',
    'label' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'icon' => null,
    'required' => false,
    'helpText' => null,
])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if ($required)
                <span style="color: var(--danger-500);">*</span>
            @endif
        </label>
    @endif

    <div style="position: relative;">
        @if ($icon)
            <i class="bi bi-{{ $icon }}" style="position: absolute; left: var(--space-4); top: 50%; transform: translateY(-50%); color: var(--text-tertiary); pointer-events: none;"></i>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            class="form-input @if($icon) ps-12 @endif @if($error) is-invalid @endif"
            placeholder="{{ $placeholder }}"
            value="{{ $value ?? old($name) }}"
            @if ($required) required @endif
            {{ $attributes }}
        />
    </div>

    @if ($error)
        <div class="invalid-feedback">
            <i class="bi bi-exclamation-circle"></i>
            {{ $error }}
        </div>
    @elseif ($helpText)
        <small style="display: block; margin-top: var(--space-2); color: var(--text-tertiary);">
            {{ $helpText }}
        </small>
    @endif
</div>
