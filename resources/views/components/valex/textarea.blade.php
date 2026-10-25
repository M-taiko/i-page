@props(['name', 'label' => null, 'value' => null, 'rows' => 4, 'help' => null, 'required' => false])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}@if ($required) <span class="tx-danger">*</span>@endif</label>
    @endif
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" @required($required)
              {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')]) }}>{{ old($name, $value) }}</textarea>
    @if ($help)<small class="form-text text-muted">{{ $help }}</small>@endif
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
