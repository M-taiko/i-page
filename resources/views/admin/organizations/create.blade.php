@extends('layouts.valex')

@section('title', __('Add New Organization'))

@section('page-header')
    <x-valex.page-header :title="__('Create New Organization')" :subtitle="__('Set up a new organization and its administrator in two quick steps')">
        <x-slot:actions>
            <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary"><i class="fe fe-arrow-left"></i> {{ __('Back to List') }}</a>
        </x-slot:actions>
    </x-valex.page-header>
@endsection

@section('content')
@php
    $blockLabels = ['hero.figure' => __('Cover'), 'hero.profile' => __('Profile'), 'channels.cards' => __('Cards'), 'channels.accordion' => __('Accordion'), 'channels.carousel' => __('Carousel'), 'channels.list' => __('List'), 'posts.timeline' => __('Timeline'), 'posts.blog' => __('Blog'), 'posts.list' => __('News list'), 'about.info' => __('Contact')];
@endphp
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">
        <form action="{{ route('admin.organizations.store') }}" method="POST" id="createOrgForm" novalidate>
            @csrf

            <ul class="nav nav-pills nav-justified mb-3" id="stepNav">
                <li class="nav-item"><span class="nav-link active" data-step="1">1. {{ __('Organization') }}</span></li>
                <li class="nav-item"><span class="nav-link" data-step="2">2. {{ __('Administrator') }}</span></li>
            </ul>

            <div class="card">
                <div class="card-body">
                    {{-- Step 1 --}}
                    <div id="panel-1">
                        <h5 class="mb-3"><i class="fe fe-briefcase"></i> {{ __('Organization Information') }}</h5>

                        <x-valex.input name="name" :label="__('Organization Name')" required autocomplete="off"
                            :placeholder="__('Example: Golden Hotel, Nile Hospital, Cairo University')"
                            :help="__('The official name of the organization — appears on all system pages')" />

                        <x-valex.textarea name="description" :label="__('Description')" rows="3" maxlength="1000"
                            :placeholder="__('Type of business, services provided')" />

                        <div class="row">
                            <div class="col-md-6"><x-valex.input name="city" :label="__('City')" /></div>
                            <div class="col-md-6"><x-valex.input name="country" :label="__('Country')" /></div>
                        </div>

                        <x-valex.select name="organization_template_id" :label="__('Organization Template')"
                            :placeholder="__('No template — start blank')"
                            :options="$templates->pluck('name', 'id')->all()"
                            :help="__('Seeds default departments and channels for this industry. You can customize everything afterwards.')" />

                        {{-- Design template: choices depend on the selected organization template's industry (ADR-012). --}}
                        <div class="form-group" id="designPicker" style="display:none">
                            <label>{{ __('Design Template') }}</label>
                            <small class="form-text text-muted mb-2">{{ __('How this organization appears to visitors and subscribers. You can change it later from the organization settings.') }}</small>
                            <input type="hidden" name="design_key" id="design_key" value="{{ old('design_key') }}">
                            <div class="row" id="designCards"></div>
                            @error('design_key')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <x-valex.input name="max_channels" type="number" :label="__('Allowed Number of Channels')" :value="4" min="1" max="1000" required
                            :help="__('The maximum number of channels the organization can create (can be modified later)')" />
                    </div>

                    {{-- Step 2 --}}
                    <div id="panel-2" style="display:none">
                        <h5 class="mb-3"><i class="fe fe-user-check"></i> {{ __('Organization Administrator') }}</h5>

                        <div class="alert alert-info">
                            <strong id="reviewName">—</strong>
                            <span class="mx-2">·</span><span id="reviewLocation">—</span>
                            <span class="mx-2">·</span><span id="reviewChannels">—</span> {{ __('channels') }}
                        </div>

                        <div class="row">
                            <div class="col-md-6"><x-valex.input name="owner_first_name" :label="__('First Name')" required autocomplete="given-name" /></div>
                            <div class="col-md-6"><x-valex.input name="owner_last_name" :label="__('Last Name')" required autocomplete="family-name" /></div>
                        </div>

                        <x-valex.input name="owner_email" type="email" :label="__('Email')" required autocomplete="email"
                            :help="__('This email will be used to log in to the system')" />

                        <div class="row">
                            <div class="col-md-6">
                                <x-valex.input name="owner_password" type="password" :label="__('Password')" required minlength="8" autocomplete="new-password" :help="__('8+ characters')" />
                            </div>
                            <div class="col-md-6">
                                <x-valex.input name="owner_password_confirmation" type="password" :label="__('Confirm Password')" required minlength="8" autocomplete="new-password" />
                                <small id="pwMatchHint" class="form-text"></small>
                            </div>
                        </div>

                        <div class="alert alert-light mb-0">
                            <i class="fe fe-info"></i> {{ __('After creating the organization, this administrator can log in immediately and manage everything for it.') }}
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="btnBack" style="visibility:hidden"><i class="fe fe-arrow-left"></i> {{ __('Back') }}</button>
                    <div>
                        <a href="{{ route('admin.organizations.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                        <button type="button" class="btn btn-primary" id="btnNext">{{ __('Continue') }} <i class="fe fe-arrow-right"></i></button>
                        <button type="submit" class="btn btn-success" id="btnSubmit" style="display:none"><i class="fe fe-check"></i> {{ __('Create Organization') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var $ = jQuery;
        var step = {{ $errors->hasAny(['owner_first_name', 'owner_last_name', 'owner_email', 'owner_password']) ? 2 : 1 }};

        function show(n) {
            step = n;
            $('#panel-1').toggle(n === 1);
            $('#panel-2').toggle(n === 2);
            $('#stepNav .nav-link').each(function () { $(this).toggleClass('active', $(this).data('step') === n); });
            $('#btnBack').css('visibility', n === 1 ? 'hidden' : 'visible');
            $('#btnNext').toggle(n === 1);
            $('#btnSubmit').toggle(n === 2);
        }

        $('#btnNext').on('click', function () {
            var name = $('#name'), max = $('#max_channels');
            if (!name.val().trim()) { name.focus(); return; }
            if (!max.val() || max.val() < 1) { max.focus(); return; }
            var loc = [$('#city').val().trim(), $('#country').val().trim()].filter(Boolean).join(', ');
            $('#reviewName').text(name.val().trim());
            $('#reviewLocation').text(loc || '—');
            $('#reviewChannels').text(max.val());
            show(2);
        });
        $('#btnBack').on('click', function () { show(1); });

        // Design templates for the selected organization template's industry.
        var industries = @json($templates->pluck('industry_key', 'id'));
        var designs = @json($designs);
        var blockLabels = @json($blockLabels);
        var classicLabel = @json(__('Classic page'));
        var classicDesc = @json(__('The standard i-Page layout.'));

        function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }

        function renderDesigns() {
            var industry = industries[$('#organization_template_id').val()];
            var list = industry ? designs[industry] : null;
            var $picker = $('#designPicker'), $cards = $('#designCards'), current = $('#design_key').val();
            $cards.empty();
            if (!list) { $picker.hide(); $('#design_key').val(''); return; }
            if (!list[current]) current = '';
            var html = '<div class="col-md-6 mb-3"><label class="card h-100 mb-0 design-card" style="cursor:pointer"><div class="card-body">'
                + '<input type="radio" name="design_pick" value="" ' + (current === '' ? 'checked' : '') + '> <strong>' + esc(classicLabel) + '</strong>'
                + '<p class="small text-muted mb-0 mt-1">' + esc(classicDesc) + '</p></div></label></div>';
            $.each(list, function (key, d) {
                var chips = $.map(d.blocks, function (b) { return '<span class="badge badge-light border mx-1 mb-1">' + esc(blockLabels[b] || b) + '</span>'; }).join('');
                html += '<div class="col-md-6 mb-3"><label class="card h-100 mb-0 design-card" style="cursor:pointer;border-top:4px solid ' + d.accent + '"><div class="card-body">'
                    + '<input type="radio" name="design_pick" value="' + esc(key) + '" ' + (current === key ? 'checked' : '') + '> '
                    + '<i class="fe fe-' + esc(d.icon) + '" style="color:' + d.accent + '"></i> <strong>' + esc(d.name) + '</strong>'
                    + '<p class="small text-muted my-1">' + esc(d.description) + '</p><div>' + chips + '</div></div></label></div>';
            });
            $cards.html(html);
            $picker.show();
            $('#design_key').val(current);
        }
        $(document).on('change', 'input[name="design_pick"]', function () { $('#design_key').val(this.value); });
        $('#organization_template_id').on('change', function () { $('#design_key').val(''); renderDesigns(); });
        renderDesigns();

        $('#owner_password, #owner_password_confirmation').on('input', function () {
            var c = $('#owner_password_confirmation').val();
            if (!c) { $('#pwMatchHint').text('').removeClass('text-success text-danger'); return; }
            var ok = $('#owner_password').val() === c;
            $('#pwMatchHint').text(ok ? '{{ __('Passwords match') }}' : '{{ __('Passwords do not match') }}')
                .toggleClass('text-success', ok).toggleClass('text-danger', !ok);
        });

        show(step);
    })();
</script>
@endpush
