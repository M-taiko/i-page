@extends('layouts.valex')

@section('title', __('Channel managers'))

@section('page-header')
    <x-valex.page-header :title="__('Channel managers')" :subtitle="__('Who runs each channel of :name. Admin and moderator roles also apply to every sub-channel.', ['name' => $organization->name])" />
@endsection

@section('content')
@php
    // Flat, indented channel list for the select, following the tree order.
    $flat = [];
    $walk = function (array $nodes, int $depth) use (&$walk, &$flat) {
        foreach ($nodes as $node) {
            $flat[$node['channel']->id] = str_repeat('— ', $depth) . $node['channel']->name . ($node['channel']->type === 'private' ? ' 🔒' : '');
            $walk($node['children'], $depth + 1);
        }
    };
    $walk($tree, 0);
@endphp
<div class="row row-sm">
    <div class="col-lg-8">
        <x-valex.card :title="__('Channels')">
            @if ($tree)
                <ul class="list-unstyled mb-0 brand-tree">
                    @foreach ($tree as $node)
                        @include('organizations.hub._tree-node', ['node' => $node, 'assignments' => $assignments, 'inherited' => []])
                    @endforeach
                </ul>
            @else
                <p class="text-center text-muted mb-0">{{ __('No channels yet.') }}</p>
            @endif
        </x-valex.card>
    </div>

    <div class="col-lg-4">
        <div style="position: sticky; top: 80px;">
            <x-valex.card :title="__('Assign a role')">
                <form action="{{ route('organizations.channel-managers.store') }}" method="POST" id="assignForm">
                    @csrf
                    <div class="form-group">
                        <label for="channel_id">{{ __('Channel') }}</label>
                        <select name="channel_id" id="channel_id" class="form-control @error('channel_id') is-invalid @enderror" required>
                            <option value="">{{ __('Choose…') }}</option>
                            @foreach ($flat as $id => $label)
                                <option value="{{ $id }}" @selected(old('channel_id') == $id)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('channel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="user_id">{{ __('Team member') }}</label>
                        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                            <option value="">{{ __('Choose…') }}</option>
                            @foreach ($team as $member)
                                <option value="{{ $member->id }}" @selected(old('user_id') == $member->id)>{{ $member->full_name }} — {{ $member->email }}</option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted">{{ __('Add people to the team first from Team.') }}</small>
                    </div>
                    <div class="form-group">
                        <label class="d-block">{{ __('Role') }}</label>
                        @foreach (['admin' => [__('Channel admin'), __('manages the channel and all its sub-channels: settings, members, QR codes and posts')],
                                   'moderator' => [__('Moderator'), __('edits, deletes and pins posts in the channel and its sub-channels')],
                                   'member' => [__('Member'), __('only the rights you tick below, in this channel only')]] as $key => [$label, $hint])
                            <div class="custom-control custom-radio mb-1">
                                <input type="radio" id="role_{{ $key }}" name="role" value="{{ $key }}" class="custom-control-input js-role" @checked(old('role', 'admin') === $key)>
                                <label class="custom-control-label" for="role_{{ $key }}"><strong>{{ $label }}</strong> — <span class="text-muted small">{{ $hint }}</span></label>
                            </div>
                        @endforeach
                    </div>
                    <div id="memberRights" class="border rounded p-2 mb-3" style="{{ old('role', 'admin') === 'member' ? '' : 'display:none' }}">
                        @foreach (['can_post' => __('Can post'), 'can_edit' => __('Can edit posts'), 'can_delete' => __('Can delete posts')] as $key => $label)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" id="{{ $key }}" name="{{ $key }}" value="1" class="custom-control-input" @checked(old($key))>
                                <label class="custom-control-label" for="{{ $key }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fe fe-check"></i> {{ __('Save') }}</button>
                </form>
            </x-valex.card>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .brand-tree ul { list-style: none; padding-inline-start: 22px; margin: 0; border-inline-start: 2px dashed rgba(100,116,139,.25); margin-inline-start: 18px; }
    .brand-tree .tree-row { display: flex; align-items: flex-start; gap: 8px; padding: 8px 6px; border-radius: 10px; }
    .brand-tree .tree-row:hover { background: rgba(100,116,139,.08); }
</style>
@endpush

@push('scripts')
<script>
    jQuery(function ($) {
        $('.js-role').on('change', function () { $('#memberRights').toggle(this.value === 'member'); });
        $('.js-assign').on('click', function () {
            $('#channel_id').val($(this).data('channel'));
            $('html, body').animate({ scrollTop: $('#assignForm').offset().top - 100 }, 200);
            $('#user_id').trigger('focus');
        });
    });
</script>
@endpush
