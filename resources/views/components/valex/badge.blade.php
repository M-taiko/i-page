@props(['variant' => 'primary', 'pill' => true])

<span {{ $attributes->merge(['class' => 'badge badge-' . $variant . ($pill ? ' badge-pill' : '')]) }}>{{ $slot }}</span>
