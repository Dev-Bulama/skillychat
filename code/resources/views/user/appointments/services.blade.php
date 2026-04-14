@extends('layouts.master')
@section('content')
<div class="row g-4">

    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>{{ translate('Appointment Services') }}</h4>
                <p class="text-muted fs-13 mb-0">{{ translate('Define the services clients can book via WhatsApp or your booking page.') }}</p>
            </div>
            <a href="{{ route('user.appointments.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Appointments') }}</a>
        </div>
    </div>

    {{-- Bot info banner --}}
    <div class="col-12">
        <div class="alert" style="background:#f0fdf4;border-left:4px solid #16a34a;padding:14px 16px;border-radius:8px;">
            <p class="fw-semibold mb-1 fs-14"><i class="bi bi-robot me-1 text-success"></i>{{ translate('Booking Bot Ready') }}</p>
            <p class="fs-13 text-muted mb-0">{{ translate('Once you add at least one service here, your WhatsApp contacts can type "book" or "appointment" to trigger the automated booking bot. The bot guides them step-by-step to book a slot, then sends an email confirmation and a 24-hour reminder automatically.') }}</p>
        </div>
    </div>

    {{-- Add service form --}}
    <div class="col-md-5">
        <div class="i-card-md h-100">
            <div class="card--header">
                <h5 class="card-title fw-semibold">{{ translate('Add New Service') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('user.appointments.service.store') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ translate('Service Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="{{ translate('e.g. 30-min Consultation') }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ translate('Description') }}</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="{{ translate('Brief description (optional)') }}">{{ old('description') }}</textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">{{ translate('Duration (mins)') }} <span class="text-danger">*</span></label>
                            <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', 60) }}" min="5" max="480">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">{{ translate('Price (0 = Free)') }}</label>
                            <div class="input-group">
                                <select name="currency" class="form-select" style="max-width:75px;">
                                    @foreach(['USD','EUR','GBP','NGN','GHS','KES','ZAR'] as $c)
                                    <option value="{{ $c }}" {{ old('currency','USD') === $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="price" class="form-control" value="{{ old('price') }}" min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="i-btn btn--md btn--primary capsuled w-100">
                        <i class="bi bi-plus-circle me-1"></i>{{ translate('Add Service') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Existing services --}}
    <div class="col-md-7">
        <div class="i-card-md">
            <div class="card--header">
                <h5 class="card-title fw-semibold">{{ translate('Your Services') }}</h5>
            </div>
            <div class="card-body">
                @if($services->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-briefcase fs-36 d-block mb-2"></i>
                    <p class="fs-14 mb-0">{{ translate('No services yet. Add one to enable the booking bot.') }}</p>
                </div>
                @else
                @foreach($services as $service)
                <div class="d-flex align-items-start gap-3 p-3 rounded-3 mb-2" style="background:#f8f9fa;border:1px solid #e5e7eb;">
                    <div class="flex-grow-1">
                        <p class="fw-semibold mb-0 fs-14">{{ $service->name }}
                            @if(!$service->is_active)<span class="badge bg-secondary ms-1 fs-11">Disabled</span>@endif
                        </p>
                        <p class="text-muted fs-12 mb-0">
                            {{ $service->duration_minutes }} min
                            @if($service->price !== null)
                            &bull; {{ $service->formattedPrice() }}
                            @else
                            &bull; Free
                            @endif
                            @if($service->description)
                            &bull; {{ Str::limit($service->description, 50) }}
                            @endif
                        </p>
                    </div>
                    <div class="d-flex gap-1 flex-shrink-0">
                        <form action="{{ route('user.appointments.service.toggle', $service->id) }}" method="post">
                            @csrf @method('PATCH')
                            <button class="i-btn btn--sm {{ $service->is_active ? 'btn--outline' : 'btn--primary' }}" title="{{ $service->is_active ? 'Disable' : 'Enable' }}">
                                <i class="bi {{ $service->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('user.appointments.service.destroy', $service->id) }}" method="post"
                            onsubmit="return confirm('Delete this service?')">
                            @csrf @method('DELETE')
                            <button class="i-btn btn--sm btn--danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
