@extends('layouts.app-modern')

@section('title', 'Add New Organization')

@section('content')
    <style>
        .wizard-shell { max-width: 880px; margin: 0 auto; }

        .back-button { margin-bottom: 1.5rem; }

        .wizard-header { text-align: center; margin-bottom: 2rem; }
        .wizard-header h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 0.25rem; }
        .wizard-header p { color: var(--text-tertiary); margin: 0; }

        /* Stepper */
        .stepper { display: flex; align-items: center; justify-content: center; margin-bottom: 2rem; }
        .stepper-step { display: flex; flex-direction: column; align-items: center; gap: 0.4rem; width: 160px; }
        .stepper-circle {
            width: 36px; height: 36px; border-radius: 50%;
            background: #e5e7eb; color: var(--text-secondary);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.9rem;
            transition: all 0.25s ease;
        }
        .stepper-label { font-size: 0.8rem; font-weight: 600; color: var(--text-tertiary); text-align: center; }
        .stepper-step.active .stepper-circle { background: var(--primary-600); color: white; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15); }
        .stepper-step.active .stepper-label { color: var(--primary-600); }
        .stepper-step.done .stepper-circle { background: var(--success-600, #059669); color: white; }
        .stepper-step.done .stepper-circle::before { content: "\f26e"; font-family: "bootstrap-icons"; }
        .stepper-line { flex: 1; height: 2px; background: #e5e7eb; max-width: 100px; margin-top: -22px; transition: background 0.25s ease; }
        .stepper-line.done { background: var(--success-600, #059669); }

        /* Cards */
        .wizard-card {
            background: white;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .wizard-step-panel { display: none; padding: 2rem; }
        .wizard-step-panel.active { display: block; }

        .panel-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .panel-title i { color: var(--primary-600); font-size: 1.3rem; }

        .form-group { margin-bottom: 1.25rem; }
        .form-group label { font-weight: 600; color: var(--text-primary); margin-bottom: 0.4rem; display: block; font-size: 0.9rem; }
        .form-label-required::after { content: " *"; color: #ef4444; }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-600);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        .form-control.is-invalid { border-color: #ef4444; }
        .form-text { font-size: 0.8rem; color: var(--text-tertiary); margin-top: 0.35rem; }
        .field-error { color: #ef4444; font-size: 0.8rem; margin-top: 0.35rem; display: flex; align-items: center; gap: 4px; }

        /* Org preview card (live) */
        .org-preview {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            background: var(--surface-bg-secondary);
            border: 1px dashed #d1d5db;
            border-radius: 10px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 1.5rem;
        }
        .org-preview-avatar {
            width: 46px; height: 46px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-600), var(--secondary-600, #7c3aed));
            color: white; font-weight: 700; font-size: 1.1rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .org-preview-name { font-weight: 700; color: var(--text-primary); font-size: 0.95rem; }
        .org-preview-meta { font-size: 0.78rem; color: var(--text-tertiary); }

        /* Password strength */
        .pw-checks { display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; margin-top: 0.5rem; }
        .pw-check { font-size: 0.78rem; color: var(--text-tertiary); display: flex; align-items: center; gap: 4px; }
        .pw-check.ok { color: var(--success-600, #059669); }
        .pw-check i { font-size: 0.85rem; }
        #pwMatchHint { font-size: 0.8rem; margin-top: 0.35rem; display: none; align-items: center; gap: 4px; }
        #pwMatchHint.ok { color: var(--success-600, #059669); display: flex; }
        #pwMatchHint.bad { color: #ef4444; display: flex; }

        /* Review (step 2) */
        .review-box { background: var(--surface-bg-secondary); border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
        .review-row { display: flex; justify-content: space-between; padding: 0.4rem 0; font-size: 0.85rem; border-bottom: 1px solid #eceef1; }
        .review-row:last-child { border-bottom: none; }
        .review-row span:first-child { color: var(--text-tertiary); }
        .review-row span:last-child { font-weight: 600; color: var(--text-primary); }

        .info-box {
            background: linear-gradient(135deg, #e0e7ff, #ede9fe);
            border-inline-start: 4px solid var(--primary-600);
            border-radius: 8px;
            padding: 1rem;
            color: var(--primary-700);
            font-size: 0.85rem;
            display: flex;
            gap: 0.6rem;
        }

        .wizard-footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem 2rem;
            border-top: 1px solid #f3f4f6;
            background: var(--surface-bg-secondary);
        }
        .wizard-footer .btn { padding: 0.6rem 1.5rem; font-weight: 600; }

        @media (max-width: 576px) {
            .stepper-step { width: auto; }
            .stepper-label { display: none; }
        }
    </style>

    <div class="wizard-shell">
        <div class="back-button">
            <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="wizard-header">
            <h1>✨ Create New Organization</h1>
            <p>Set up a new organization and its administrator in two quick steps</p>
        </div>

        @if($errors->any())
            <x-alert-modern type="danger" dismissible>
                <strong>⚠️ Error in input data:</strong>
                <ul class="mb-0" style="padding-left: 1.5rem; margin-top: 0.5rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert-modern>
        @endif

        <!-- Stepper -->
        <div class="stepper">
            <div class="stepper-step active" id="stepper-1">
                <div class="stepper-circle">1</div>
                <div class="stepper-label">Organization</div>
            </div>
            <div class="stepper-line" id="stepper-line"></div>
            <div class="stepper-step" id="stepper-2">
                <div class="stepper-circle">2</div>
                <div class="stepper-label">Administrator</div>
            </div>
        </div>

        <form action="{{ route('admin.organizations.store') }}" method="POST" id="createOrgForm" novalidate>
            @csrf

            <div class="wizard-card">
                <!-- Step 1: Organization Information -->
                <div class="wizard-step-panel active" id="panel-1">
                    <div class="panel-title"><i class="bi bi-building"></i> Organization Information</div>

                    <div class="org-preview">
                        <div class="org-preview-avatar" id="orgPreviewAvatar">?</div>
                        <div>
                            <div class="org-preview-name" id="orgPreviewName">{{ __('New Organization') }}</div>
                            <div class="org-preview-meta" id="orgPreviewMeta">{{ __('This is how it will appear across the platform') }}</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label-required">Organization Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}"
                               placeholder="Example: Golden Hotel, Nile Hospital, Cairo University"
                               required autocomplete="off">
                        <p class="form-text">The official name of the organization — appears on all system pages</p>
                        @error('name')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3" maxlength="1000"
                                  placeholder="Detailed description of the organization, type of business, services provided">{{ old('description') }}</textarea>
                        <p class="form-text"><span id="descCount">0</span>/1000 characters</p>
                        @error('description')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror"
                                       id="city" name="city" value="{{ old('city') }}" placeholder="Cairo">
                                @error('city')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror"
                                       id="country" name="country" value="{{ old('country') }}" placeholder="Egypt">
                                @error('country')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="organization_template_id">Organization Template</label>
                        <select class="form-select @error('organization_template_id') is-invalid @enderror"
                                id="organization_template_id" name="organization_template_id">
                            <option value="">No template — start blank</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" @selected(old('organization_template_id') == $template->id)>
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="form-text">Seeds default departments and channels for this industry. You can customize everything afterwards.</p>
                        @error('organization_template_id')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                    </div>

                    <div class="form-group mb-0">
                        <label for="max_channels" class="form-label-required">Allowed Number of Channels</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('max_channels') is-invalid @enderror"
                                   id="max_channels" name="max_channels" value="{{ old('max_channels', 4) }}"
                                   min="1" max="1000" required>
                            <span class="input-group-text">channels</span>
                        </div>
                        <p class="form-text">The maximum number of channels the organization can create (can be modified later)</p>
                        @error('max_channels')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Step 2: Administrator Information -->
                <div class="wizard-step-panel" id="panel-2">
                    <div class="panel-title"><i class="bi bi-person-badge"></i> Organization Administrator</div>

                    <div class="review-box" id="reviewBox">
                        <div class="review-row"><span>Organization</span><span id="reviewName">—</span></div>
                        <div class="review-row"><span>Location</span><span id="reviewLocation">—</span></div>
                        <div class="review-row"><span>Max Channels</span><span id="reviewChannels">—</span></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="owner_first_name" class="form-label-required">First Name</label>
                                <input type="text" class="form-control @error('owner_first_name') is-invalid @enderror"
                                       id="owner_first_name" name="owner_first_name" value="{{ old('owner_first_name') }}"
                                       placeholder="Ahmed" required autocomplete="given-name">
                                @error('owner_first_name')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="owner_last_name" class="form-label-required">Last Name</label>
                                <input type="text" class="form-control @error('owner_last_name') is-invalid @enderror"
                                       id="owner_last_name" name="owner_last_name" value="{{ old('owner_last_name') }}"
                                       placeholder="Muhammad" required autocomplete="family-name">
                                @error('owner_last_name')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="owner_email" class="form-label-required">Email</label>
                        <input type="email" class="form-control @error('owner_email') is-invalid @enderror"
                               id="owner_email" name="owner_email" value="{{ old('owner_email') }}"
                               placeholder="admin@organization.com" required autocomplete="email">
                        <p class="form-text">This email will be used to log in to the system</p>
                        @error('owner_email')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="owner_password" class="form-label-required">Password</label>
                                <input type="password" class="form-control @error('owner_password') is-invalid @enderror"
                                       id="owner_password" name="owner_password" placeholder="••••••••"
                                       required minlength="8" autocomplete="new-password">
                                <div class="pw-checks">
                                    <span class="pw-check" id="pwCheckLen"><i class="bi bi-circle"></i> 8+ characters</span>
                                </div>
                                @error('owner_password')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="owner_password_confirmation" class="form-label-required">Confirm Password</label>
                                <input type="password" class="form-control @error('owner_password_confirmation') is-invalid @enderror"
                                       id="owner_password_confirmation" name="owner_password_confirmation" placeholder="••••••••"
                                       required minlength="8" autocomplete="new-password">
                                <div id="pwMatchHint"><i class="bi bi-check-circle"></i> <span>Passwords match</span></div>
                                @error('owner_password_confirmation')<div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="info-box">
                        <i class="bi bi-lightbulb"></i>
                        <div>After creating the organization, this administrator can log in immediately and manage everything for it.</div>
                    </div>
                </div>
            </div>

            <div class="wizard-card" style="margin-top: 0; border-top: none; border-top-left-radius: 0; border-top-right-radius: 0;">
                <div class="wizard-footer">
                    <button type="button" class="btn btn-outline-secondary" id="btnBack" style="visibility: hidden;">
                        <i class="bi bi-arrow-left"></i> Back
                    </button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="button" class="btn btn-primary" id="btnNext">
                            Continue <i class="bi bi-arrow-right"></i>
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit" style="display: none;">
                            <i class="bi bi-check-circle"></i> Create Organization
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

<script>
    (function () {
        const panels = { 1: document.getElementById('panel-1'), 2: document.getElementById('panel-2') };
        const steppers = { 1: document.getElementById('stepper-1'), 2: document.getElementById('stepper-2') };
        const stepperLine = document.getElementById('stepper-line');
        const btnBack = document.getElementById('btnBack');
        const btnNext = document.getElementById('btnNext');
        const btnSubmit = document.getElementById('btnSubmit');
        let currentStep = {{ $errors->hasAny(['owner_first_name', 'owner_last_name', 'owner_email', 'owner_password']) ? 2 : 1 }};

        function showStep(step) {
            currentStep = step;
            Object.keys(panels).forEach(k => panels[k].classList.toggle('active', Number(k) === step));
            Object.keys(steppers).forEach(k => {
                steppers[k].classList.toggle('active', Number(k) === step);
                steppers[k].classList.toggle('done', Number(k) < step);
            });
            stepperLine.classList.toggle('done', step > 1);
            btnBack.style.visibility = step === 1 ? 'hidden' : 'visible';
            btnNext.style.display = step === 1 ? 'inline-flex' : 'none';
            btnSubmit.style.display = step === 2 ? 'inline-flex' : 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function validateStep1() {
            const name = document.getElementById('name');
            const maxChannels = document.getElementById('max_channels');
            if (!name.value.trim()) { name.focus(); return false; }
            if (!maxChannels.value || maxChannels.value < 1) { maxChannels.focus(); return false; }
            return true;
        }

        btnNext.addEventListener('click', function () {
            if (!validateStep1()) return;
            updateReview();
            showStep(2);
        });

        btnBack.addEventListener('click', function () {
            showStep(1);
        });

        if (currentStep === 2) showStep(2);

        // Live org preview
        const nameInput = document.getElementById('name');
        const cityInput = document.getElementById('city');
        const countryInput = document.getElementById('country');
        const previewAvatar = document.getElementById('orgPreviewAvatar');
        const previewName = document.getElementById('orgPreviewName');
        const previewMeta = document.getElementById('orgPreviewMeta');

        function updatePreview() {
            const name = nameInput.value.trim();
            previewAvatar.textContent = name ? name.charAt(0).toUpperCase() : '?';
            previewName.textContent = name || 'New Organization';
            const parts = [cityInput.value.trim(), countryInput.value.trim()].filter(Boolean);
            previewMeta.textContent = parts.length ? parts.join(', ') : 'This is how it will appear across the platform';
        }
        [nameInput, cityInput, countryInput].forEach(el => el.addEventListener('input', updatePreview));
        updatePreview();

        // Description character counter
        const descInput = document.getElementById('description');
        const descCount = document.getElementById('descCount');
        function updateDescCount() { descCount.textContent = descInput.value.length; }
        descInput.addEventListener('input', updateDescCount);
        updateDescCount();

        // Review summary
        function updateReview() {
            document.getElementById('reviewName').textContent = nameInput.value.trim() || '—';
            const parts = [cityInput.value.trim(), countryInput.value.trim()].filter(Boolean);
            document.getElementById('reviewLocation').textContent = parts.length ? parts.join(', ') : '—';
            document.getElementById('reviewChannels').textContent = document.getElementById('max_channels').value || '—';
        }
        updateReview();

        // Password strength + match
        const pwInput = document.getElementById('owner_password');
        const pwConfirm = document.getElementById('owner_password_confirmation');
        const pwCheckLen = document.getElementById('pwCheckLen');
        const pwMatchHint = document.getElementById('pwMatchHint');

        function updatePasswordChecks() {
            const ok = pwInput.value.length >= 8;
            pwCheckLen.classList.toggle('ok', ok);
            pwCheckLen.innerHTML = (ok ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-circle"></i>') + ' 8+ characters';
        }

        function updatePasswordMatch() {
            if (!pwConfirm.value) { pwMatchHint.className = ''; return; }
            const match = pwInput.value === pwConfirm.value;
            pwMatchHint.className = match ? 'ok' : 'bad';
            pwMatchHint.innerHTML = match
                ? '<i class="bi bi-check-circle"></i> <span>Passwords match</span>'
                : '<i class="bi bi-x-circle"></i> <span>Passwords do not match</span>';
        }

        pwInput.addEventListener('input', function () { updatePasswordChecks(); updatePasswordMatch(); });
        pwConfirm.addEventListener('input', updatePasswordMatch);
        updatePasswordChecks();
    })();
</script>
@endsection
