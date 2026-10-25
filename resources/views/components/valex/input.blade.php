@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'help' => null, 'required' => false])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}@if ($required) <span class="tx-danger">*</span>@endif</label>
    @endif
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
           @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
           @required($required)
           {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')]) }}>
    @if ($help)<small class="form-text text-muted">{{ $help }}</small>@endif
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
