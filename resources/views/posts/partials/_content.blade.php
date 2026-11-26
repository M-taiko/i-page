{{-- Post title/body/media/badges in the post's card style (ADR-013).
     Expects $post. Optional: $limit (truncate body), $url (link title and text), $showWidgets (render interactions),
     $cardStyle (channel override, ADR-014), $hideMedia (compact layouts), $hideReactions.
     "compact" (the default template): title and text beside a thumbnail. --}}
@include('posts.partials._styles')
@include('posts.partials._image-viewer')
@php
    $style = ($cardStyle ?? null) ?: ($post->card_style ?: 'compact');
    $hideMedia = $hideMedia ?? false;
    $badge = $post->badge();
    $accent = $badge['color'] ?? null;
    $format = $post->post_format ?: 'standard';
    $formats = config('posts.formats');
    $text = ($limit ?? null) ? Str::limit($post->body, $limit) : $post->body;
    $url = $url ?? null;
    // Compact cards show one thumbnail (the first image, with a +N badge for galleries) instead of the media block.
    $thumb = $style === 'compact' && ! $hideMedia ? $post->coverUrl() : null;
    $extraImages = $thumb ? max(0, $post->galleryImages()->count() - 1) : 0;
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

    @if ($style === 'compact')
        <div class="pf-row {{ $thumb ? 'has-thumb' : '' }}">
            <div class="pf-row-text">
                @if ($post->title)<div class="pf-title">@if ($url)<a class="pf-link" href="{{ $url }}">{{ $post->title }}</a>@else{{ $post->title }}@endif</div>@endif
                @if ($text)<div class="pf-body">@if ($url)<a class="pf-link" href="{{ $url }}">{{ $text }}</a>@else{{ $text }}@endif</div>@endif
            </div>
            @if ($thumb)
                <a href="{{ $url ?: $thumb }}" class="pf-thumb" @if(!$url) data-full-image @endif aria-label="{{ $url ? __('View post') : __('View full image') }}">
                    <img src="{{ $thumb }}" alt="" loading="lazy">
                    @if ($extraImages)<span class="pf-thumb-more">+{{ $extraImages }}</span>@endif
                </a>
            @endif
        </div>
    @else
        @if ($style === 'image' && ! $hideMedia)
            @include('posts.partials._media')
        @endif

        @if ($style === 'gradient')
            <div class="pf-text">
                @if ($post->title)<div class="pf-title">@if ($url)<a class="pf-link" href="{{ $url }}">{{ $post->title }}</a>@else{{ $post->title }}@endif</div>@endif
                <div class="pf-body">@if($url)<a class="pf-link" href="{{ $url }}">{{ $text }}</a>@else{{ $text }}@endif</div>
            </div>
        @else
            @if ($post->title)<div class="pf-title">@if ($url)<a class="pf-link" href="{{ $url }}">{{ $post->title }}</a>@else{{ $post->title }}@endif</div>@endif
            @if ($text)<div class="pf-body">@if ($url)<a class="pf-link" href="{{ $url }}">{{ $text }}</a>@else{{ $text }}@endif</div>@endif
        @endif

        @if ($style !== 'image' && ! $hideMedia)
            @include('posts.partials._media')
        @endif
    @endif

    @if ($showWidgets ?? false)
        @include('posts.partials._widgets')
    @endif

    @php
        $bookingServiceId = $post->option('booking_service_id');
        $showBookingCta = $post->option('promotion') && $bookingServiceId && $post->channel
            && app(\App\Services\IndustryModuleService::class)->enabled($post->channel,'bookings')
            && $post->channel->organization->appointmentServices()->whereKey($bookingServiceId)->where('is_active',true)->exists();
    @endphp
    @if($showBookingCta)
        <a href="{{ route('guest.appointments.create',[$post->channel,'service'=>$bookingServiceId]) }}" class="btn btn-primary btn-block mt-3" style="border-radius:14px;padding:12px;font-weight:800;background:linear-gradient(135deg,#5146e5,#7c3aed);border:0">
            <i class="bi bi-calendar2-check"></i> {{ $post->option('booking_button_label',__('Book this offer')) }}
        </a>
    @endif
</div>
