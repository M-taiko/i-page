{{-- Post title/body/media/badges in the post's card style (ADR-013).
     Expects $post. Optional: $limit (truncate body), $url (link title and text), $showWidgets (render interactions). --}}
@include('posts.partials._styles')
@php
    $style = $post->card_style ?: 'classic';
    $badge = $post->badge();
    $accent = $badge['color'] ?? null;
    $format = $post->post_format ?: 'standard';
    $formats = config('posts.formats');
    $text = ($limit ?? null) ? Str::limit($post->body, $limit) : $post->body;
    $url = $url ?? null;
@endphp
<div class="pf-content pf-style-{{ $style }}" @if ($accent) style="--pf-accent: {{ $accent }}" @endif>
    @if ($badge || in_array($post->priority, ['high', 'critical'], true) || $format !== 'standard')
        <div class="pf-badges">
            @if ($badge)<span class="pf-badge"><i class="bi bi-tag-fill" style="font-size:10px"></i> {{ $badge['label'] }}</span>@endif
            @if (in_array($post->priority, ['high', 'critical'], true))
                <span class="pf-badge pf-prio-{{ $post->priority }}">{{ $post->priority === 'critical' ? __('Critical priority') : __('High priority') }}</span>
            @endif
            @if ($format !== 'standard')
                <span class="pf-badge pf-chip">{{ __($formats[$format]['name']) }}</span>
            @endif
        </div>
    @endif

    @if ($style === 'image')
        @include('posts.partials._media')
    @endif

    @if ($style === 'gradient')
        <div class="pf-text">
            @if ($post->title)<div class="pf-title">@if ($url)<a class="pf-link" href="{{ $url }}">{{ $post->title }}</a>@else{{ $post->title }}@endif</div>@endif
            <div class="pf-body">{{ $text }}</div>
        </div>
    @else
        @if ($post->title)<div class="pf-title">@if ($url)<a class="pf-link" href="{{ $url }}">{{ $post->title }}</a>@else{{ $post->title }}@endif</div>@endif
        @if ($text)<div class="pf-body">@if ($url)<a class="pf-link" href="{{ $url }}">{{ $text }}</a>@else{{ $text }}@endif</div>@endif
    @endif

    @if ($style !== 'image')
        @include('posts.partials._media')
    @endif

    @if ($showWidgets ?? false)
        @include('posts.partials._widgets')
    @endif
</div>
