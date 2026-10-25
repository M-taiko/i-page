@props(['health'])

<span class="badge badge-{{ $health['variant'] }} badge-pill" title="{{ __('Health score') }}: {{ $health['score'] }}/100">{{ $health['label'] }} · {{ $health['score'] }}</span>
