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

        {{-- Filters + Search --}}
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <select id="filter-platform" class="form-select form-select-sm">
                    <option value="">{{ translate('All Platforms') }}</option>
                    @foreach($platforms as $val => $label)
                    <option value="{{ $val }}" {{ request('platform') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter-type" class="form-select form-select-sm">
                    <option value="">{{ translate('All Types') }}</option>
                    @foreach($service_types as $val => $label)
                    <option value="{{ $val }}" {{ request('service_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select id="filter-provider" class="form-select form-select-sm">
                    <option value="">{{ translate('All Providers') }}</option>
                    @foreach($providers as $p)
                    <option value="{{ $p->id }}" {{ request('provider_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="search-input" class="form-control"
                        placeholder="{{ translate('Search services…') }}"
                        value="{{ request('search') }}" autocomplete="off">
                    <button class="btn btn-outline-secondary" id="clear-search" type="button" title="Clear">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Bulk action bar --}}
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap" id="bulk-bar">
            <div class="form-check mb-0">
                <input type="checkbox" id="select-all" class="form-check-input">
                <label class="form-check-label fs-13" for="select-all">{{ translate('Select All') }}</label>
            </div>
            <span class="text-muted fs-13" id="selected-count">0 {{ translate('selected') }}</span>
            <button class="i-btn btn--sm btn--success" id="bulk-activate" disabled>
                <i class="bi bi-toggle-on me-1"></i>{{ translate('Activate') }}
            </button>
            <button class="i-btn btn--sm btn--danger" id="bulk-deactivate" disabled>
                <i class="bi bi-toggle-off me-1"></i>{{ translate('Deactivate') }}
            </button>
            <span class="ms-auto fs-13 text-muted" id="results-count"></span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle fs-14">
                <thead>
                    <tr>
                        <th style="width:36px;"></th>
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
                <tbody id="services-tbody">
                    @include('admin.smm_boost.partials.services_rows', ['services' => $services])
                </tbody>
            </table>
        </div>
        <div id="pagination-wrap">
            {{ $services->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    var baseUrl    = '{{ route('admin.smm.services') }}';
    var bulkUrl    = '{{ route('admin.smm.services.bulk-toggle') }}';
    var csrfToken  = '{{ csrf_token() }}';
    var debounceTimer;

    // ── Gather current filter values ──────────────────────────────────────
    function getParams() {
        return {
            platform:     document.getElementById('filter-platform').value,
            service_type: document.getElementById('filter-type').value,
            provider_id:  document.getElementById('filter-provider').value,
            search:       document.getElementById('search-input').value.trim(),
            page:         1,
        };
    }

    // ── AJAX fetch table rows ─────────────────────────────────────────────
    function fetchServices(params) {
        var url = new URL(baseUrl);
        Object.entries(params).forEach(([k, v]) => { if (v) url.searchParams.set(k, v); });

        document.getElementById('services-tbody').style.opacity = '0.4';

        fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                document.getElementById('services-tbody').innerHTML = data.html;
                document.getElementById('services-tbody').style.opacity = '1';
                document.getElementById('pagination-wrap').innerHTML   = data.pagination;
                document.getElementById('results-count').textContent   = data.total + ' service(s)';
                updateSelectAll();
            })
            .catch(() => { document.getElementById('services-tbody').style.opacity = '1'; });
    }

    // ── Wire filter controls ──────────────────────────────────────────────
    ['filter-platform', 'filter-type', 'filter-provider'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', function () {
            fetchServices(getParams());
        });
    });

    document.getElementById('search-input').addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            fetchServices(getParams());
        }, 300);
    });

    document.getElementById('clear-search').addEventListener('click', function () {
        document.getElementById('search-input').value = '';
        fetchServices(getParams());
    });

    // ── Bulk selection ────────────────────────────────────────────────────
    function getChecked() {
        return Array.from(document.querySelectorAll('.service-check:checked')).map(c => c.value);
    }

    function updateBulkBar() {
        var checked = getChecked().length;
        document.getElementById('selected-count').textContent = checked + ' selected';
        document.getElementById('bulk-activate').disabled   = checked === 0;
        document.getElementById('bulk-deactivate').disabled = checked === 0;
    }

    function updateSelectAll() {
        document.getElementById('select-all').checked = false;
        updateBulkBar();
    }

    document.getElementById('services-tbody').addEventListener('change', function (e) {
        if (e.target.classList.contains('service-check')) updateBulkBar();
    });

    document.getElementById('select-all').addEventListener('change', function () {
        document.querySelectorAll('.service-check').forEach(cb => { cb.checked = this.checked; });
        updateBulkBar();
    });

    // ── Bulk action ───────────────────────────────────────────────────────
    function bulkAction(action) {
        var ids = getChecked();
        if (!ids.length) return;

        fetch(bulkUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ ids: ids, action: action }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                fetchServices(getParams());
            } else {
                alert(data.message || 'Error');
            }
        });
    }

    document.getElementById('bulk-activate').addEventListener('click',   function () { bulkAction('activate'); });
    document.getElementById('bulk-deactivate').addEventListener('click', function () { bulkAction('deactivate'); });

    // Show initial count
    document.getElementById('results-count').textContent = '{{ $services->total() }} service(s)';
})();
</script>
@endpush
