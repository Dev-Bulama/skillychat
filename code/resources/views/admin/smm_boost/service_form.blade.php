@extends('admin.layouts.master')
@section('content')
<form action="{{ $service ? route('admin.smm.services.update', $service->id) : route('admin.smm.services.store') }}"
    method="post" class="add-listing-form">
    @csrf
    @if($service) @method('PUT') @endif

    <div class="i-card-md">
        <div class="card--header">
            <h4 class="card-title">
                {{ $service ? translate('Edit Service') : translate('Add SMM Service') }}
            </h4>
            <a href="{{ route('admin.smm.services') }}" class="i-btn btn--sm btn--outline">
                {{ translate('Back') }}
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-inner">
                        <label>{{ translate('Provider') }} <small class="text-danger">*</small></label>
                        <select name="provider_id" required class="form-select">
                            <option value="">{{ translate('Select Provider') }}</option>
                            @foreach($providers as $p)
                            <option value="{{ $p->id }}" {{ old('provider_id', $service?->provider_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-inner">
                        <label>{{ translate("Provider's Service ID") }} <small class="text-danger">*</small></label>
                        <input type="text" name="provider_service_id" required
                            placeholder="{{ translate('e.g. 1234') }}"
                            value="{{ old('provider_service_id', $service?->provider_service_id) }}">
                        <small class="text-muted">{{ translate('The numeric ID assigned by the provider for this service.') }}</small>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-inner">
                        <label>{{ translate('Service Name') }} <small class="text-danger">*</small></label>
                        <input type="text" name="name" required
                            placeholder="{{ translate('e.g. Instagram Followers — High Quality') }}"
                            value="{{ old('name', $service?->name) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-inner">
                        <label>{{ translate('Platform') }} <small class="text-danger">*</small></label>
                        <select name="platform" required class="form-select">
                            <option value="">{{ translate('Select Platform') }}</option>
                            @foreach($platforms as $val => $label)
                            <option value="{{ $val }}" {{ old('platform', $service?->platform) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-inner">
                        <label>{{ translate('Service Type') }} <small class="text-danger">*</small></label>
                        <select name="service_type" required class="form-select">
                            <option value="">{{ translate('Select Type') }}</option>
                            @foreach($service_types as $val => $label)
                            <option value="{{ $val }}" {{ old('service_type', $service?->service_type) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inner">
                        <label>{{ translate('Price per 1000') }} <small class="text-danger">*</small></label>
                        <input type="number" step="0.0001" min="0" name="price_per_1000" required
                            placeholder="1.50"
                            value="{{ old('price_per_1000', $service?->price_per_1000) }}">
                        <small class="text-muted">{{ translate('Amount charged to user per 1000 units.') }}</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inner">
                        <label>{{ translate('Min Quantity') }} <small class="text-danger">*</small></label>
                        <input type="number" min="1" name="min_quantity" required
                            placeholder="100"
                            value="{{ old('min_quantity', $service?->min_quantity) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inner">
                        <label>{{ translate('Max Quantity') }} <small class="text-danger">*</small></label>
                        <input type="number" min="1" name="max_quantity" required
                            placeholder="100000"
                            value="{{ old('max_quantity', $service?->max_quantity) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-inner">
                        <label>{{ translate('Delivery Estimate') }}</label>
                        <input type="text" name="delivery_estimate"
                            placeholder="{{ translate('e.g. 1-3 days') }}"
                            value="{{ old('delivery_estimate', $service?->delivery_estimate) }}">
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-end pb-3">
                    <div class="d-flex align-items-center gap-3">
                        <label class="mb-0">{{ translate('Active') }}</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status"
                                {{ old('status', $service?->isActive()) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-inner">
                        <label>{{ translate('Description') }}</label>
                        <textarea name="description" rows="3"
                            placeholder="{{ translate('Optional service description shown to users') }}">{{ old('description', $service?->description) }}</textarea>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <button type="submit" class="i-btn btn--md btn--primary" data-anim="ripple">
                        {{ $service ? translate('Update Service') : translate('Save Service') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
