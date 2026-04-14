@extends('layouts.master')
@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>{{ translate('Appointments') }}</h4>
                <p class="text-muted fs-13 mb-0">{{ translate('Manage appointments booked by clients via WhatsApp or manually.') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('user.appointments.services') }}" class="i-btn btn--sm btn--outline">
                    <i class="bi bi-briefcase me-1"></i>{{ translate('Services') }}
                </a>
                <a href="{{ route('user.appointments.create') }}" class="i-btn btn--sm btn--primary">
                    <i class="bi bi-plus-circle me-1"></i>{{ translate('New Appointment') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="col-6 col-md-3">
        <div class="i-card text-center py-3">
            <div class="fs-26 fw-bold text-primary">{{ $stats['total'] }}</div>
            <div class="fs-12 text-muted">{{ translate('Total') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="i-card text-center py-3">
            <div class="fs-26 fw-bold text-warning">{{ $stats['today'] }}</div>
            <div class="fs-12 text-muted">{{ translate('Today') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="i-card text-center py-3">
            <div class="fs-26 fw-bold text-success">{{ $stats['upcoming'] }}</div>
            <div class="fs-12 text-muted">{{ translate('Upcoming') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="i-card text-center py-3">
            <div class="fs-26 fw-bold text-info">{{ $stats['confirmed'] }}</div>
            <div class="fs-12 text-muted">{{ translate('Confirmed') }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="col-12">
        <div class="i-card-md">
            <div class="card-body">
                <form class="row g-2" method="GET">
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">{{ translate('All Statuses') }}</option>
                            @foreach(['pending','confirmed','cancelled','completed','no_show'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                    </div>
                    <div class="col-auto">
                        <button class="i-btn btn--sm btn--primary">{{ translate('Filter') }}</button>
                        <a href="{{ route('user.appointments.index') }}" class="i-btn btn--sm btn--outline ms-1">{{ translate('Clear') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="col-12">
        <div class="i-card-md">
            <div class="card-body p-0">
                @if($appointments->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-40 d-block mb-2"></i>
                    <p class="mb-0">{{ translate('No appointments found.') }}</p>
                    <a href="{{ route('user.appointments.create') }}" class="i-btn btn--sm btn--primary mt-3">{{ translate('Add First Appointment') }}</a>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ translate('Contact') }}</th>
                                <th>{{ translate('Service') }}</th>
                                <th>{{ translate('Date & Time') }}</th>
                                <th>{{ translate('Source') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th class="text-end">{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $apt)
                            <tr>
                                <td>
                                    <p class="fw-semibold mb-0 fs-14">{{ $apt->contact_name }}</p>
                                    <small class="text-muted">{{ $apt->contact_phone ?: $apt->contact_email }}</small>
                                </td>
                                <td class="fs-14">{{ $apt->service?->name ?? '—' }}</td>
                                <td>
                                    <p class="mb-0 fs-14">{{ $apt->appointment_at->format('D, M j, Y') }}</p>
                                    <small class="text-muted">{{ $apt->appointment_at->format('g:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary fs-11">{{ ucfirst($apt->booked_via) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $apt->statusBadgeClass() }} fs-11">{{ ucfirst(str_replace('_',' ',$apt->status)) }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('user.appointments.show', $apt->uid) }}" class="i-btn btn--sm btn--outline">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $appointments->links() }}</div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
