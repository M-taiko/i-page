@extends('layouts.guest-valex', ['primary' => $options['primary_color'], 'secondary' => $options['secondary_color'], 'backUrl' => route('guest.organization-detail', $organization)])

@section('title', $channel->name)
@section('meta_description', \Illuminate\Support\Str::limit($channel->description ?? __(':channel — updates from :org on i-Page.', ['channel' => $channel->name, 'org' => $organization->name]), 180))

@push('styles')
    <link href="{{ asset('assets/valex/plugins/timeline/css/timeline.min.css') }}" rel="stylesheet">
@endpush

@section('content')
    @foreach ($design['blocks'] as $block)
        {{-- Sub-channel / sibling blocks are skipped when there is nothing to show. --}}
        @continue(str_starts_with($block, 'channels.') && $channelGroups->isEmpty())
        @include('guest.valex.blocks.' . $block)
    @endforeach

    @if ($parentChannel)
        <p class="mt-3"><a href="{{ route('guest.channel-detail', [$organization, $parentChannel->slug]) }}"><i class="fe fe-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ __('Back to :name', ['name' => $parentChannel->name]) }}</a></p>
    @endif
@endsection

@section('modals')
    @guest
        <div class="modal fade" id="signinModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content text-center p-4">
                    <i class="fe fe-lock tx-40 text-primary"></i>
                    <h5 class="mt-2">{{ __('Sign in required') }}</h5>
                    <p class="text-muted">{{ __('Sign in to follow organizations, subscribe to channels and join the conversation.') }}</p>
                    <div class="d-flex">
                        <button type="button" class="btn btn-light flex-fill mx-1" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <a href="{{ route('login') }}" class="btn btn-primary flex-fill mx-1">{{ __('Sign In') }}</a>
                    </div>
                </div>
            </div>
        </div>
    @endguest
@endsection
