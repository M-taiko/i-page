@props(['scene' => null])
@php
    $scene ??= \App\Support\DashboardIllustrations::forRoute(request()->route()?->getName());
    $editing = request()->routeIs('*.create', '*.edit');
    $arabic = auth()->check() && auth()->user()->language === 'ar';
@endphp
<div class="ip-page-art">
    <div class="ip-art-scene" aria-hidden="true">
        <x-illustration :name="$scene" :size="144" />
        @if($editing)
        <svg class="ip-art-edit" width="30" height="30" viewBox="0 0 32 32" fill="none" aria-hidden="true" focusable="false"><rect x="1" y="1" width="30" height="30" rx="10" fill="var(--ill-paper)" stroke="var(--ill-line)"/><path d="m9 20 1-5 10-10 5 5-10 10-6 1Zm9-13 5 5M9 25h15" stroke="var(--ill-primary)" stroke-width="2" stroke-linejoin="round"/></svg>
        @endif
    </div>
    <button type="button" class="ip-art-motion" data-art-motion aria-pressed="false" data-pause-label="{{ $arabic ? 'إيقاف حركة الرسومات' : 'Pause illustrations' }}" data-play-label="{{ $arabic ? 'تشغيل حركة الرسومات' : 'Play illustrations' }}" aria-label="{{ $arabic ? 'إيقاف حركة الرسومات' : 'Pause illustrations' }}" title="{{ $arabic ? 'إيقاف حركة الرسومات' : 'Pause illustrations' }}">
        <svg class="ip-art-pause-icon" width="12" height="12" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 3v10M12 3v10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        <svg class="ip-art-play-icon" width="12" height="12" viewBox="0 0 16 16" aria-hidden="true"><path d="m5 3 8 5-8 5Z" fill="currentColor"/></svg>
    </button>
</div>
