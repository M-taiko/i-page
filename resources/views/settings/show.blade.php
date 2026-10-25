@extends('layouts.valex')

@section('title', __('Settings'))

@section('page-header')
    <x-valex.page-header :title="__('Settings')" :subtitle="__('Manage your account and preferences')" />
@endsection

@section('content')
@php $orgId = session('current_organization_id'); @endphp

<div class="row">
    <div class="col-lg-3">
        <div class="card">
            <div class="list-group list-group-flush" id="settingsNav" role="tablist">
                <a class="list-group-item list-group-item-action active" data-toggle="list" href="#profile" role="tab"><i class="fe fe-user"></i> {{ __('Profile') }}</a>
                <a class="list-group-item list-group-item-action" data-toggle="list" href="#appearance" role="tab"><i class="fe fe-droplet"></i> {{ __('Appearance') }}</a>
                <a class="list-group-item list-group-item-action" data-toggle="list" href="#notifications" role="tab"><i class="fe fe-bell"></i> {{ __('Notifications') }}</a>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="tab-content">
            {{-- Profile --}}
            <div class="tab-pane active" id="profile" role="tabpanel">
                <x-valex.card :title="__('Profile Settings')">
                    <form action="{{ route('dashboard.settings.updateProfile', $orgId) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6"><x-valex.input name="first_name" :label="__('First Name')" :value="$user->first_name" required /></div>
                            <div class="col-md-6"><x-valex.input name="last_name" :label="__('Last Name')" :value="$user->last_name" required /></div>
                        </div>
                        <x-valex.input name="mobile" :label="__('Mobile')" :value="$user->mobile" />
                        <div class="row">
                            <div class="col-md-6">
                                <x-valex.select name="gender" :label="__('Gender')" :placeholder="__('Select...')" :value="$user->gender"
                                    :options="['male' => __('Male'), 'female' => __('Female'), 'other' => __('Other')]" />
                            </div>
                            <div class="col-md-6"><x-valex.input name="nationality" :label="__('Nationality')" :value="$user->nationality" /></div>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </form>
                </x-valex.card>
            </div>

            {{-- Appearance --}}
            <div class="tab-pane" id="appearance" role="tabpanel">
                <x-valex.card :title="__('Appearance')">
                    <form action="{{ route('dashboard.settings.updateAppearance', $orgId) }}" method="POST">
                        @csrf
                        <x-valex.select name="theme" :label="__('Theme')" :value="$user->theme"
                            :options="['auto' => __('Auto'), 'light' => __('Light'), 'dark' => __('Dark')]" />
                        <x-valex.select name="language" :label="__('Language')" :value="$user->language"
                            :options="['en' => 'English', 'ar' => 'العربية']" />
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </form>
                </x-valex.card>
            </div>

            {{-- Notifications --}}
            <div class="tab-pane" id="notifications" role="tabpanel">
                <x-valex.card :title="__('Notifications')">
                    <form action="{{ route('dashboard.settings.updateNotifications', $orgId) }}" method="POST">
                        @csrf
                        @foreach ([
                            ['notify_posts', 'notification_posts', __('Post Notifications'), __('Get notified when new posts are published')],
                            ['notify_channels', 'notification_channels', __('Channel Updates'), __('Get notified about channel updates')],
                            ['notify_mentions', 'notification_mentions', __('Mentions'), __('Get notified when you\'re mentioned')],
                            ['notify_groups', 'notification_groups', __('Group Activities'), __('Get notified about group activities')],
                        ] as [$field, $pref, $label, $help])
                            <div class="custom-control custom-switch py-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                <input type="checkbox" class="custom-control-input" id="{{ $field }}" name="{{ $field }}" @checked($user->preferences?->{$pref})>
                                <label class="custom-control-label" for="{{ $field }}">
                                    <strong class="d-block">{{ $label }}</strong>
                                    <small class="text-muted">{{ $help }}</small>
                                </label>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-primary mt-3">{{ __('Save Changes') }}</button>
                    </form>
                </x-valex.card>
            </div>
        </div>
    </div>
</div>
@endsection
