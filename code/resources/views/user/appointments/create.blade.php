@extends('layouts.master')
@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-7">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-calendar-plus me-2"></i>{{ translate('New Appointment') }}</h4>
                <a href="{{ route('user.appointments.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}</a>
            </div>
            <div class="card-body">
                <form action="{{ route('user.appointments.store') }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Contact Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="contact_name" class="form-control @error('contact_name') is-invalid @enderror"
                                value="{{ old('contact_name') }}" placeholder="John Doe">
                            @error('contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Phone Number') }}</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone') }}" placeholder="+234...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Email') }}</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email') }}" placeholder="john@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Service') }}</label>
                            <select name="service_id" class="form-select">
                                <option value="">{{ translate('— No specific service —') }}</option>
                                @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} ({{ $service->duration_minutes }}min)
                                </option>
                                @endforeach
                            </select>
                            @if($services->isEmpty())
                            <small class="text-muted fs-12">
                                <a href="{{ route('user.appointments.services') }}">{{ translate('Add services') }}</a> {{ translate('to enable the booking bot.') }}
                            </small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Date & Time') }} <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="appointment_at" class="form-control @error('appointment_at') is-invalid @enderror"
                                value="{{ old('appointment_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}">
                            @error('appointment_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="{{ translate('Any additional notes…') }}">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-calendar-check me-1"></i>{{ translate('Create Appointment') }}
                            </button>
                            <a href="{{ route('user.appointments.index') }}" class="i-btn btn--md btn--outline capsuled">{{ translate('Cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
