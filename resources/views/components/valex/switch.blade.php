@props(['name', 'label', 'checked' => false, 'value' => '1', 'help' => null])

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" class="custom-control-input" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" @checked(old($name, $checked))>
        <label class="custom-control-label" for="{{ $name }}">{{ $label }}</label>
    </div>
    @if ($help)<small class="form-text text-muted">{{ $help }}</small>@endif
</div>
