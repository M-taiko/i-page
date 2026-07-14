@extends('layouts.app-modern')

@section('content')
<div class="container-lg py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h3 mb-4">Create Support Ticket</h1>

            <form action="{{ route('tickets.store') }}" method="POST">
                @csrf

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Brief description of your issue" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" placeholder="Provide detailed information about your issue" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="complaint" @selected(old('type') === 'complaint')>Complaint</option>
                                    <option value="feedback" @selected(old('type') === 'feedback')>Feedback</option>
                                    <option value="suggestion" @selected(old('type') === 'suggestion')>Suggestion</option>
                                    <option value="request" @selected(old('type') === 'request')>Request</option>
                                    <option value="bug" @selected(old('type') === 'bug')>Bug Report</option>
                                    <option value="other" @selected(old('type') === 'other')>Other</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                    <option value="low" @selected(old('priority', 'medium') === 'low')>Low</option>
                                    <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                                    <option value="high" @selected(old('priority', 'medium') === 'high')>High</option>
                                    <option value="urgent" @selected(old('priority', 'medium') === 'urgent')>Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('customer_email') is-invalid @enderror" id="customer_email" name="customer_email" value="{{ old('customer_email', auth()->user()->email ?? '') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="customer_phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}">
                            </div>
                        </div>

                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle"></i> Your ticket will be reviewed by our team and you'll receive updates via email.
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Submit Ticket
                    </button>
                    <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
