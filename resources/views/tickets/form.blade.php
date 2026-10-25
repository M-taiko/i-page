@extends('layouts.valex')

@section('title', __('Create Support Ticket'))

@section('page-header')
    <x-valex.page-header :title="__('Create Support Ticket')">
        <x-slot:actions>
            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ route('tickets.store') }}" method="POST">
            @csrf
            <x-valex.card>
                <x-valex.input name="title" :label="__('Subject')" required :placeholder="__('Brief description of your issue')" />
                <x-valex.textarea name="description" :label="__('Description')" rows="5" required :placeholder="__('Provide detailed information about your issue')" />

                <div class="row">
                    <div class="col-md-6">
                        <x-valex.select name="type" :label="__('Type')" required :placeholder="__('Select Type')"
                            :options="['complaint' => __('Complaint'), 'feedback' => __('Feedback'), 'suggestion' => __('Suggestion'), 'request' => __('Request'), 'bug' => __('Bug Report'), 'other' => __('Other')]" />
                    </div>
                    <div class="col-md-6">
                        <x-valex.select name="priority" :label="__('Priority')" value="medium"
                            :options="['low' => __('Low'), 'medium' => __('Medium'), 'high' => __('High'), 'urgent' => __('Urgent')]" />
                    </div>
                    <div class="col-md-6"><x-valex.input name="customer_email" type="email" :label="__('Email')" :value="auth()->user()->email ?? ''" /></div>
                    <div class="col-md-6"><x-valex.input name="customer_phone" type="tel" :label="__('Phone')" /></div>
                </div>

                <div class="alert alert-info small mb-0"><i class="fe fe-info"></i> {{ __('Your ticket will be reviewed by our team and you\'ll receive updates via email.') }}</div>
            </x-valex.card>

            <button type="submit" class="btn btn-primary"><i class="fe fe-check"></i> {{ __('Submit Ticket') }}</button>
            <a href="{{ route('tickets.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection
