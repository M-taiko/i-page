@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'help' => null, 'required' => false])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}@if ($required) <span class="tx-danger">*</span>@endif</label>
    @endif
    <select id="{{ $name }}" name="{{ $name }}" @required($required)
            {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')]) }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) old($name, $value) === (string) $optValue)>{{ $optLabel }}</option>
        @endforeach
        {{ $slot }}
    </select>
    @if ($help)<small class="form-text text-muted">{{ $help }}</small>@endif
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
