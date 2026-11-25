@php
    $selectedChannel = $channel ?? $channels->firstWhere('id', (int) old('channel_id')) ?? $channels->first();
@endphp
@push('styles')<link rel="stylesheet" href="{{ asset('css/post-composer.css') }}">@endpush
<form class="post-composer" id="postForm" method="POST" action="{{ $action }}" enctype="multipart/form-data">
@csrf
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>{{ __('Please review the following fields.') }}</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="composer-layout"><div class="composer-editor">
<x-valex.card :title="__('Publishing channel')" :subtitle="__('Only channels you can publish in are shown.')">
<label for="channel_id">{{ __('Channel') }}</label>
<select id="channel_id" name="channel_id" class="form-control" required @disabled($channels->count() === 1)>
@foreach($channels as $availableChannel)<option value="{{ $availableChannel->id }}" @selected($selectedChannel->id === $availableChannel->id)>{{ $availableChannel->name }} @if($availableChannel->parentChannels->isNotEmpty()) / {{ $availableChannel->parentChannels->pluck('name')->implode('، ') }} @endif — {{ $availableChannel->organization?->name }}</option>@endforeach
</select>
@if($channels->count() === 1)<input type="hidden" name="channel_id" value="{{ $selectedChannel->id }}"><small class="form-text text-muted">{{ __('Your publishing channel is selected automatically.') }}</small>@endif
</x-valex.card>
@include('tenant.channels.posts._post-fields', ['targetName' => __('Your message'), 'targetDescription' => __('Create content, then choose how it appears.')])
@include('tenant.channels.posts._publish-options', ['channel' => $selectedChannel, 'dynamicChannel' => !$channel])
<x-valex.card :title="__('More options')">
<div class="row"><div class="col-md-6"><x-valex.select name="post_type" :label="__('Type')" :options="['announcement'=>__('Announcement'),'news'=>__('News'),'offer'=>__('Offer'),'emergency'=>__('Emergency')]" /></div>
<div class="col-md-6"><x-valex.select name="language" :label="__('Language')" :value="auth()->user()->language ?: 'en'" :options="['ar'=>__('Arabic'),'en'=>__('English')]" /></div></div>
<label for="media">{{ __('Attachments') }}</label><input id="media" type="file" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="form-control"><small class="form-text text-muted">{{ __('Images or PDFs') }} · {{ __('Up to 8 files, 5MB each.') }}</small>
<div class="custom-control custom-switch mt-3"><input class="custom-control-input" type="checkbox" id="requires_acknowledgment" name="requires_acknowledgment" value="1" @checked(old('requires_acknowledgment'))><label class="custom-control-label" for="requires_acknowledgment">{{ __('Requires Acknowledgment') }}</label></div>
</x-valex.card>
</div><aside class="composer-preview-column"><div class="composer-preview-sticky">
<div class="composer-preview-heading"><span class="composer-eyebrow">{{ __('LIVE PREVIEW') }}</span><span class="badge badge-light">{{ __('Illustrative preview') }}</span></div>
<article id="composerPreview" class="composer-preview" data-style="compact">
<div class="preview-author"><span class="preview-avatar"><i class="fe fe-message-circle"></i></span><div><strong id="previewChannel">{{ $selectedChannel->name }}</strong><small>{{ __('Just now') }}</small></div></div>
<span id="previewBadge" class="badge badge-primary" hidden></span><span id="previewPin" class="badge badge-warning" hidden>📌 {{ __('Highlight') }}</span>
<h3 id="previewTitle" data-empty="{{ __('Your headline appears here') }}">{{ __('Your headline appears here') }}</h3>
<p id="previewBody" data-empty="{{ __('Start writing to preview your post.') }}">{{ __('Start writing to preview your post.') }}</p>
<img id="previewImage" alt="{{ __('Selected image preview') }}" hidden>
<div id="previewExtra"></div><div id="previewReactions" class="preview-reactions">👍</div>
</article><p class="small text-muted mt-3">{{ __('The final layout adapts to the reader’s screen.') }}</p>
<div class="composer-publish-panel"><x-valex.select name="status" :label="__('Publish As')" :value="'published'" :options="['published'=>__('Publish Immediately'),'draft'=>__('Draft'),'pending_approval'=>__('Send for Approval')]" />
<button type="submit" class="btn btn-primary btn-block"><i class="fe fe-send"></i> {{ __('Save Post') }}</button><a href="{{ $backUrl }}" class="btn btn-light btn-block">{{ __('Cancel') }}</a></div>
</div></aside></div></form>
@push('scripts')<script src="{{ asset('js/post-composer.js') }}"></script>@endpush
