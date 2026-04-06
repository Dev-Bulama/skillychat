@forelse($services as $service)
<tr data-id="{{ $service->id }}">
    <td>
        <input type="checkbox" class="service-check form-check-input" value="{{ $service->id }}">
    </td>
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
<tr id="no-results-row">
    <td colspan="9" class="text-center py-4">
        @include('admin.partials.not_found')
    </td>
</tr>
@endforelse
