@extends('layouts.master')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-9">

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1 fw-semibold">
                        <i class="bi bi-geo-alt me-2" style="color:#4285f4;"></i>
                        {{ translate('Edit Business Info') }}
                    </h4>
                    <p class="text-muted mb-0 fs-14">{{ $location->display_name }}</p>
                </div>
                <a href="{{ route('user.google-business.index') }}" class="i-btn btn--sm btn--outline">
                    <i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}
                </a>
            </div>

            @if(session('status'))
                <div class="alert alert-{{ session('type','success') === 'error' ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('user.google-business.location.update', $location->id) }}" method="post">
                @csrf

                {{-- Basic Info --}}
                <div class="i-card-md mb-4">
                    <div class="card--header">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>{{ translate('Basic Information') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ translate('Display Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="display_name" class="form-control @error('display_name') is-invalid @enderror"
                                    value="{{ old('display_name', $location->display_name) }}" required>
                                @error('display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ translate('Phone Number') }}</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $location->phone) }}" placeholder="+1 555 000 0000">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ translate('Website URL') }}</label>
                                <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                                    value="{{ old('website', $location->website) }}" placeholder="https://example.com">
                                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ translate('Business Description') }}</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="4" placeholder="{{ translate('Describe your business (up to 750 characters)...') }}"
                                    maxlength="750">{{ old('description', $location->description) }}</textarea>
                                <small class="text-muted fs-12">{{ translate('Max 750 characters') }}</small>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Business Hours --}}
                <div class="i-card-md mb-4">
                    <div class="card--header">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-clock me-2"></i>{{ translate('Business Hours') }}</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $dayKeys  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                            $dayNames = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

                            // Parse existing hours_json periods
                            $hoursData = [];
                            if (! empty($location->hours_json['periods'])) {
                                foreach ($location->hours_json['periods'] as $period) {
                                    $dayKey = strtolower($period['openDay'] ?? '');
                                    $openH  = str_pad($period['openTime']['hours'] ?? 9, 2, '0', STR_PAD_LEFT);
                                    $openM  = str_pad($period['openTime']['minutes'] ?? 0, 2, '0', STR_PAD_LEFT);
                                    $closeH = str_pad($period['closeTime']['hours'] ?? 17, 2, '0', STR_PAD_LEFT);
                                    $closeM = str_pad($period['closeTime']['minutes'] ?? 0, 2, '0', STR_PAD_LEFT);
                                    $hoursData[$dayKey] = [
                                        'open'       => true,
                                        'open_time'  => "{$openH}:{$openM}",
                                        'close_time' => "{$closeH}:{$closeM}",
                                    ];
                                }
                            }
                        @endphp
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ translate('Open') }}</th>
                                        <th>{{ translate('Day') }}</th>
                                        <th>{{ translate('Opens At') }}</th>
                                        <th>{{ translate('Closes At') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dayKeys as $i => $key)
                                    @php $day = $hoursData[$key] ?? ['open' => false, 'open_time' => '09:00', 'close_time' => '17:00']; @endphp
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input hours-toggle"
                                                    name="hours[{{ $key }}][open]" value="1"
                                                    id="open_{{ $key }}"
                                                    data-target="{{ $key }}"
                                                    {{ $day['open'] ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <label class="form-check-label fw-semibold" for="open_{{ $key }}">
                                                {{ translate($dayNames[$i]) }}
                                            </label>
                                        </td>
                                        <td>
                                            <input type="time" name="hours[{{ $key }}][open_time]"
                                                class="form-control form-control-sm hours-field-{{ $key }}"
                                                value="{{ $day['open_time'] }}"
                                                style="width:120px;"
                                                {{ !$day['open'] ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="time" name="hours[{{ $key }}][close_time]"
                                                class="form-control form-control-sm hours-field-{{ $key }}"
                                                value="{{ $day['close_time'] }}"
                                                style="width:120px;"
                                                {{ !$day['open'] ? 'disabled' : '' }}>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Sync info --}}
                @if($location->last_sync_at)
                <p class="text-muted fs-13 mb-3">
                    <i class="bi bi-clock me-1"></i>{{ translate('Last synced with Google') }}: {{ $location->last_sync_at->format('M j, Y H:i') }}
                </p>
                @endif

                <div class="d-flex gap-2">
                    <button type="submit" class="i-btn btn--md btn--primary">
                        <i class="bi bi-save me-1"></i>{{ translate('Save Changes') }}
                    </button>
                    <a href="{{ route('user.google-business.index') }}" class="i-btn btn--md btn--outline">
                        {{ translate('Cancel') }}
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
document.querySelectorAll('.hours-toggle').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var key = this.dataset.target;
        document.querySelectorAll('.hours-field-' + key).forEach(function(field) {
            field.disabled = !cb.checked;
        });
    });
});
</script>
@endpush

@endsection
