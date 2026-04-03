@extends('admin.layouts.master')
@section('content')
<div class="i-card-md">
    <div class="card--header">
        <h4 class="card-title">{{ translate('SMM API Providers') }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.smm.setup-guide') }}" class="i-btn btn--sm btn--outline">
                <i class="bi bi-book me-1"></i>{{ translate('Setup Guide') }}
            </a>
            <a href="{{ route('admin.smm.providers.create') }}" class="i-btn btn--sm btn--primary">
                <i class="bi bi-plus-circle me-1"></i>{{ translate('Add Provider') }}
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ translate('Name') }}</th>
                        <th>{{ translate('API URL') }}</th>
                        <th>{{ translate('Services') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th>{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $provider)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $provider->name }}</strong>
                        </td>
                        <td>
                            <code class="fs-12">{{ Str::limit($provider->api_url, 40) }}</code>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $provider->services()->count() }}</span>
                        </td>
                        <td>
                            @if($provider->isActive())
                                <span class="badge bg-success">{{ translate('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ translate('Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="i-btn btn--sm btn--outline test-provider"
                                    data-id="{{ $provider->id }}"
                                    data-url="{{ route('admin.smm.providers.test', $provider->id) }}">
                                    <i class="bi bi-wifi me-1"></i>{{ translate('Test') }}
                                </button>
                                <button class="i-btn btn--sm btn--secondary import-services"
                                    data-id="{{ $provider->id }}"
                                    data-url="{{ route('admin.smm.providers.import', $provider->id) }}">
                                    <i class="bi bi-download me-1"></i>{{ translate('Import') }}
                                </button>
                                <button class="i-btn btn--sm btn--outline redetect-services"
                                    data-id="{{ $provider->id }}"
                                    data-url="{{ route('admin.smm.providers.redetect', $provider->id) }}"
                                    title="{{ translate('Re-detect platform & type from service names') }}">
                                    <i class="bi bi-arrow-repeat me-1"></i>{{ translate('Re-detect') }}
                                </button>
                                <a href="{{ route('admin.smm.providers.edit', $provider->id) }}"
                                    class="i-btn btn--sm btn--primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.smm.providers.destroy', $provider->id) }}" method="post"
                                    onsubmit="return confirm('{{ translate('Delete this provider?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="i-btn btn--sm btn--danger" type="submit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            @include('admin.partials.not_found')
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $providers->links() }}
    </div>
</div>

{{-- Result Modal --}}
<div class="modal fade" id="apiResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="apiResultTitle">{{ translate('API Result') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="apiResultBody">
                <div class="text-center py-3"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    function showModal(title, html) {
        document.getElementById('apiResultTitle').textContent = title;
        document.getElementById('apiResultBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('apiResultModal')).show();
    }

    document.querySelectorAll('.test-provider').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = this.dataset.url;
            showModal('{{ translate("Testing Connection...") }}', '<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(function (data) {
                    var cls = data.success ? 'success' : 'danger';
                    showModal('{{ translate("Connection Test") }}',
                        '<div class="alert alert-' + cls + '">' + data.message + '</div>');
                }).catch(function (e) {
                    showModal('{{ translate("Error") }}', '<div class="alert alert-danger">' + e.message + '</div>');
                });
        });
    });

    document.querySelectorAll('.redetect-services').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('{{ translate("Re-detect platform and service type for all imported services of this provider? This will update services that were tagged as \'other\'.") }}')) return;
            var url = this.dataset.url;
            showModal('{{ translate("Re-detecting...") }}', '<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>');
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            }).then(r => r.json())
              .then(function (data) {
                var cls = data.success ? 'success' : 'danger';
                showModal('{{ translate("Re-detect Result") }}',
                    '<div class="alert alert-' + cls + '">' + data.message + '</div>');
              }).catch(function (e) {
                showModal('{{ translate("Error") }}', '<div class="alert alert-danger">' + e.message + '</div>');
              });
        });
    });

    document.querySelectorAll('.import-services').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('{{ translate("Import services from this provider? This may take a moment.") }}')) return;
            var url = this.dataset.url;
            showModal('{{ translate("Importing...") }}', '<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>');
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            }).then(r => r.json())
              .then(function (data) {
                var cls = data.success ? 'success' : 'danger';
                showModal('{{ translate("Import Result") }}',
                    '<div class="alert alert-' + cls + '">' + data.message + '</div>');
              }).catch(function (e) {
                showModal('{{ translate("Error") }}', '<div class="alert alert-danger">' + e.message + '</div>');
              });
        });
    });
})();
</script>
@endpush
