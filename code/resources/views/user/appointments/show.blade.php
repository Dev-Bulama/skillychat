@extends('layouts.master')
@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-7">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-calendar-event me-2"></i>{{ translate('Appointment Details') }}</h4>
                <a href="{{ route('user.appointments.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}</a>
            </div>
            <div class="card-body">

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <p class="text-muted fs-12 mb-1">{{ translate('Contact Name') }}</p>
                        <p class="fw-semibold mb-0">{{ $appointment->contact_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted fs-12 mb-1">{{ translate('Status') }}</p>
                        <span class="badge {{ $appointment->statusBadgeClass() }}">{{ ucfirst(str_replace('_',' ',$appointment->status)) }}</span>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted fs-12 mb-1">{{ translate('Phone') }}</p>
                        <p class="fw-semibold mb-0">{{ $appointment->contact_phone ?: '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted fs-12 mb-1">{{ translate('Email') }}</p>
                        <p class="fw-semibold mb-0">{{ $appointment->contact_email ?: '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted fs-12 mb-1">{{ translate('Service') }}</p>
                        <p class="fw-semibold mb-0">{{ $appointment->service?->name ?? '—' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted fs-12 mb-1">{{ translate('Date & Time') }}</p>
                        <p class="fw-semibold mb-0">{{ $appointment->appointment_at->format('l, F j, Y \a\t g:i A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted fs-12 mb-1">{{ translate('Booked Via') }}</p>
                        <span class="badge bg-secondary">{{ ucfirst($appointment->booked_via) }}</span>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted fs-12 mb-1">{{ translate('Reminders') }}</p>
                        <p class="mb-0 fs-13">
                            Confirmation: <span class="badge {{ $appointment->confirmation_sent ? 'bg-success' : 'bg-secondary' }}">{{ $appointment->confirmation_sent ? 'Sent' : 'Pending' }}</span>
                            &nbsp; 24h: <span class="badge {{ $appointment->reminder_24h_sent ? 'bg-success' : 'bg-secondary' }}">{{ $appointment->reminder_24h_sent ? 'Sent' : 'Pending' }}</span>
                        </p>
                    </div>
                    @if($appointment->notes)
                    <div class="col-12">
                        <p class="text-muted fs-12 mb-1">{{ translate('Notes') }}</p>
                        <p class="mb-0 fs-14">{{ $appointment->notes }}</p>
                    </div>
                    @endif
                </div>

                {{-- Update status --}}
                <hr>
                <form action="{{ route('user.appointments.status', $appointment->uid) }}" method="post" class="d-flex gap-2 align-items-center flex-wrap">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm" style="max-width:180px;">
                        @foreach(['pending','confirmed','cancelled','completed','no_show'] as $s)
                        <option value="{{ $s }}" {{ $appointment->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                    <button class="i-btn btn--sm btn--primary">{{ translate('Update Status') }}</button>
                    <form action="{{ route('user.appointments.destroy', $appointment->uid) }}" method="post" class="ms-auto"
                        onsubmit="return confirm('Delete this appointment?')">
                        @csrf @method('DELETE')
                        <button class="i-btn btn--sm btn--danger"><i class="bi bi-trash"></i> {{ translate('Delete') }}</button>
                    </form>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
