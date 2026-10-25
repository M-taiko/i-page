{{-- Compact badge / format / priority chips for list-style post renderings. Expects $post. --}}
@php
    $chipBadge = $post->badge();
    $chipFormat = $post->post_format ?: 'standard';
@endphp
@if ($chipBadge)<span class="badge" style="background: {{ $chipBadge['color'] }}; color: #fff;">{{ $chipBadge['label'] }}</span>@endif
@if (in_array($post->priority, ['high', 'critical'], true))<span class="badge {{ $post->priority === 'critical' ? 'badge-danger' : 'badge-warning' }}">{{ $post->priority === 'critical' ? __('Critical') : __('High') }}</span>@endif
@if ($chipFormat !== 'standard')<span class="badge badge-light border"><i class="fe fe-{{ config('posts.formats')[$chipFormat]['icon'] }}"></i> {{ __(config('posts.formats')[$chipFormat]['name']) }}</span>@endif
