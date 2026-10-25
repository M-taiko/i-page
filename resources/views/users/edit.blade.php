@extends('layouts.valex')

@section('title', __('Edit User'))

@section('page-header')
    <x-valex.page-header :title="__('Edit User')" :subtitle="$user->full_name">
        <x-slot:actions>
            <a href="{{ route('dashboard.users.show', [$organization, $user]) }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-7">
        <x-valex.card>
            <form action="{{ route('dashboard.users.update', [$organization, $user]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6"><x-valex.input name="first_name" :label="__('First Name')" :value="$user->first_name" required /></div>
                    <div class="col-md-6"><x-valex.input name="last_name" :label="__('Last Name')" :value="$user->last_name" required /></div>
                </div>
                <x-valex.input name="email" type="email" :label="__('Email')" :value="$user->email" required />
                <x-valex.input name="mobile" :label="__('Mobile')" :value="$user->mobile" />
                <div class="row">
                    <div class="col-md-6">
                        <x-valex.select name="gender" :label="__('Gender')" :placeholder="__('— Select —')" :value="$user->gender"
                            :options="['male' => __('Male'), 'female' => __('Female'), 'other' => __('Other')]" />
                    </div>
                    <div class="col-md-6"><x-valex.input name="nationality" :label="__('Nationality')" :value="$user->nationality" /></div>
                </div>

                <button type="submit" class="btn btn-primary">{{ __('Update User') }}</button>
                <a href="{{ route('dashboard.users.show', [$organization, $user]) }}" class="btn btn-light">{{ __('Cancel') }}</a>
            </form>
        </x-valex.card>
    </div>
</div>
@endsection
