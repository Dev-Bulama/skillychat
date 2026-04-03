@extends('admin.layouts.master')
@section('content')
<div class="i-card-md">
    <div class="card--header">
        <h4 class="card-title">{{ translate('SMM Services') }}</h4>
        <a href="{{ route('admin.smm.services.create') }}" class="i-btn btn--sm btn--primary">
            <i class="bi bi-plus-circle me-1"></i>{{ translate('Add Service') }}
        </a>
    </div>
    <div class="card-body">

        {{-- Filters --}}
        <form method="get" class="row g-2 mb-4">
            <div class="col-md-3">
                <select name="platform" class="form-select form-select-sm">
                    <option value="">{{ translate('All Platforms') }}</option>
                    @foreach($platforms as $val => $label)
                    <option value="{{ $val }}" {{ request('platform') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="service_type" class="form-select form-select-sm">
                    <option value="">{{ translate('All Types') }}</option>
                    @foreach($service_types as $val => $label)
                    <option value="{{ $val }}" {{ request('service_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="provider_id" class="form-select form-select-sm">
                    <option value="">{{ translate('All Providers') }}</option>
                    @foreach($providers as $p)
                    <option value="{{ $p->id }}" {{ request('provider_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="i-btn btn--sm btn--outline w-100">{{ translate('Filter') }}</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle fs-14">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('Name') }}</th>
                        <th>{{ translate('Platform') }}</th>
                        <th>{{ translate('Type') }}</th>
                        <th>{{ translate('Price/1000') }}</th>
                        <th>{{ translate('Min / Max') }}</th>
                        <th>{{ translate('Provider') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th>{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <span title="{{ $service->description }}">{{ Str::limit($service->name, 40) }}</span>
                            @if($service->delivery_estimate)
                            <br><small class="text-muted"><i class="bi bi-clock"></i> {{ $service->delivery_estimate }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-primary">{{ ucfirst($service->platform) }}</span></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $service->service_type)) }}</td>
                        <td>${{ number_format($service->price_per_1000, 2) }}</td>
                        <td>{{ number_format($service->min_quantity) }} – {{ number_format($service->max_quantity) }}</td>
                        <td>{{ $service->provider?->name ?? '—' }}</td>
                        <td>
                            @if($service->isActive())
                                <span class="badge bg-success">{{ translate('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ translate('Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('admin.smm.services.toggle', $service->id) }}"
                                    class="i-btn btn--sm {{ $service->isActive() ? 'btn--outline' : 'btn--success' }}"
                                    title="{{ $service->isActive() ? translate('Disable') : translate('Enable') }}">
                                    <i class="bi bi-{{ $service->isActive() ? 'toggle-off' : 'toggle-on' }}"></i>
                                </a>
                                <a href="{{ route('admin.smm.services.edit', $service->id) }}"
                                    class="i-btn btn--sm btn--primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.smm.services.destroy', $service->id) }}" method="post"
                                    onsubmit="return confirm('{{ translate('Delete this service?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="i-btn btn--sm btn--danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            @include('admin.partials.not_found')
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $services->withQueryString()->links() }}
    </div>
</div>
@endsection
