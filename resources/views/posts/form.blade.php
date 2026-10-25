@extends('layouts.valex')

@section('title', isset($post) ? __('Edit Post') : __('Create Post'))

@section('page-header')
    <x-valex.page-header :title="isset($post) ? __('Edit Post') : __('Create Post')"
        :subtitle="isset($post) ? __('Update your announcement or update before it goes live') : __('Share an announcement, news update, or offer with your audience')">
        <x-slot:actions>
            <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <form action="{{ isset($post) ? route('posts.update', $post) : route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if (isset($post)) @method('PUT') @endif

            <x-valex.card :title="__('Content')">
                <x-valex.input name="title" :label="__('Title')" :value="$post->title ?? ''" required :placeholder="__('Give your post a clear title')" />
                <x-valex.input name="summary" :label="__('Summary') . ' (' . __('optional') . ')'" :value="$post->summary ?? ''" :placeholder="__('A short one-line summary')" />
                <x-valex.textarea name="body" :label="__('Content')" rows="8" :value="$post->body ?? ''" required :placeholder="__('Write your announcement...')" />

                <div class="form-group">
                    <label for="image">{{ __('Cover Image') }} <span class="text-muted">({{ __('optional — shown at the top of the post') }})</span></label>
                    @if (isset($post) && $post->image_path)
                        <div class="mb-2">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($post->image_path) }}" alt="" class="rounded" style="max-height: 160px;">
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1">
                                <label class="custom-control-label" for="remove_image">{{ __('Remove current image') }}</label>
                            </div>
                        </div>
                    @endif
                    <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp" class="form-control">
                </div>

                <div class="form-group mb-0">
                    <label for="media">{{ __('Attachments') }} <span class="text-muted">({{ __('optional') }})</span></label>
                    <input type="file" id="media" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="form-control">
                    <small class="form-text text-muted">{{ __('Images or PDFs') }}</small>
                </div>
            </x-valex.card>

            <x-valex.card :title="__('Settings')">
                <div class="row">
                    <div class="col-md-6">
                        <x-valex.select name="post_type" :label="__('Type')" :value="$post->post_type ?? ''"
                            :options="['announcement' => __('Announcement'), 'news' => __('News'), 'offer' => __('Offer'), 'emergency' => __('Emergency')]" />
                    </div>
                    <div class="col-md-6">
                        <x-valex.select name="priority" :label="__('Priority')" :value="$post->priority ?? ''"
                            :options="['low' => __('Low'), 'medium' => __('Medium'), 'high' => __('High'), 'critical' => __('Critical')]" />
                    </div>
                    <div class="col-md-6">
                        <x-valex.select name="language" :label="__('Language')" :value="$post->language ?? ''"
                            :options="['en' => __('English'), 'ar' => __('Arabic')]" />
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="custom-control custom-switch mt-3">
                            <input type="checkbox" class="custom-control-input" id="requires_acknowledgment" name="requires_acknowledgment" @checked(old('requires_acknowledgment', $post->requires_acknowledgment ?? false))>
                            <label class="custom-control-label" for="requires_acknowledgment">{{ __('Requires Acknowledgment') }}</label>
                        </div>
                    </div>
                </div>

                @if (! isset($post) || $post->status === 'draft')
                    <x-valex.select name="status" :label="__('Publish As')"
                        :options="['draft' => __('Draft'), 'pending_approval' => __('Send for Approval'), 'published' => __('Publish Immediately')]"
                        :help="__('Drafts are only visible to you. Sending for approval requires an admin review. Immediate publish is available to admins only.')" />
                @endif
            </x-valex.card>

            <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ isset($post) ? __('Update Post') : __('Create Post') }}</button>
            <a href="{{ route('posts.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
