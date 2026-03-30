@extends('admin.layouts.master')

@push('style-include')
<style nonce="{{ csp_nonce() }}">
.upd-card { background:#fff; border-radius:12px; padding:28px 32px; box-shadow:0 2px 12px rgba(0,0,0,.07); }
.upd-title { font-size:1.1rem; font-weight:700; color:#1e293b; margin-bottom:4px; }
.upd-sub   { font-size:.85rem; color:#64748b; margin-bottom:20px; }

/* Drop zone */
.upd-dropzone {
    border:2px dashed #cbd5e1; border-radius:10px; padding:36px 20px;
    text-align:center; cursor:pointer; transition:.2s;
    background:#f8fafc; position:relative;
}
.upd-dropzone:hover, .upd-dropzone.drag { border-color:#6366f1; background:#eef2ff; }
.upd-dropzone input { position:absolute; inset:0; opacity:0; cursor:pointer; }
.upd-dropzone i { font-size:2.4rem; color:#94a3b8; display:block; margin-bottom:10px; }
.upd-dropzone .dz-label { font-size:.95rem; color:#475569; }
.upd-dropzone .dz-hint  { font-size:.78rem; color:#94a3b8; margin-top:4px; }
.upd-dropzone .dz-file  { font-size:.9rem; color:#6366f1; font-weight:600; display:none; margin-top:8px; }

/* Progress */
.upd-progress { margin-top:18px; display:none; }
.upd-bar-wrap  { height:8px; border-radius:4px; background:#e2e8f0; overflow:hidden; }
.upd-bar       { height:100%; width:0; border-radius:4px; background:#6366f1; transition:width .25s ease; }
.upd-bar.done  { background:#10b981; }
.upd-bar.fail  { background:#ef4444; }
.upd-pct       { font-size:.78rem; color:#64748b; margin-top:6px; text-align:right; }
.upd-status    { font-size:.87rem; margin-top:10px; padding:10px 14px; border-radius:7px; display:none; }
.upd-status.ok   { background:#f0fdf4; color:#166534; }
.upd-status.fail { background:#fef2f2; color:#991b1b; }
.upd-status.info { background:#eff6ff; color:#1e40af; }

/* Buttons */
.upd-btn { display:inline-flex; align-items:center; gap:6px; padding:10px 22px;
           border-radius:8px; font-size:.9rem; font-weight:600; border:none; cursor:pointer;
           transition:.15s; }
.upd-btn-primary { background:#6366f1; color:#fff; }
.upd-btn-primary:hover:not(:disabled) { background:#4f46e5; }
.upd-btn-primary:disabled { opacity:.55; cursor:not-allowed; }

/* Version badge */
.upd-version { display:inline-flex; align-items:center; gap:8px;
               background:#f1f5f9; border-radius:8px; padding:8px 16px;
               font-size:.9rem; color:#334155; margin-bottom:20px; }
.upd-version b { color:#1e293b; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8">

            {{-- Current version --}}
            <div class="upd-card mb-4">
                <div class="upd-title">{{ translate('System Update') }}</div>
                <div class="upd-sub">{{ translate('Upload a ZIP file to apply updates. Your .env file and user uploads are never overwritten.') }}</div>

                <div class="upd-version">
                    <i class="las la-tag"></i>
                    {{ translate('Current version') }}: <b>v{{ site_settings('app_version', '1.0') }}</b>
                </div>

                <form id="updateForm" enctype="multipart/form-data">
                    @csrf

                    {{-- Drop zone --}}
                    <div class="upd-dropzone" id="dropZone">
                        <input type="file" name="updateFile" id="updateFile" accept=".zip">
                        <i class="las la-cloud-upload-alt"></i>
                        <div class="dz-label">{{ translate('Drop ZIP here or click to browse') }}</div>
                        <div class="dz-hint">{{ translate('GitHub archives, plain project ZIPs, or patch ZIPs all accepted') }}</div>
                        <div class="dz-file" id="dzFile"></div>
                    </div>

                    {{-- Progress --}}
                    <div class="upd-progress" id="progressWrap">
                        <div class="upd-bar-wrap">
                            <div class="upd-bar" id="progressBar"></div>
                        </div>
                        <div class="upd-pct" id="progressPct">0%</div>
                    </div>

                    {{-- Status message --}}
                    <div class="upd-status" id="statusMsg"></div>

                    {{-- Submit --}}
                    <div class="mt-4 text-end">
                        <button type="submit" class="upd-btn upd-btn-primary" id="submitBtn" disabled>
                            <i class="las la-upload"></i> {{ translate('Upload & Apply Update') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- Click & Update (secondary) --}}
            <div class="upd-card">
                <div class="upd-title">{{ translate('Check for Updates') }}</div>
                <div class="upd-sub">{{ translate('Automatically check and install updates from the update server (requires valid license).') }}</div>

                <button class="upd-btn upd-btn-primary" id="checkBtn">
                    <i class="las la-sync"></i> {{ translate('Check Now') }}
                </button>

                <div class="upd-status mt-3" id="checkStatus"></div>
                <div id="updateList" class="mt-3"></div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    'use strict';

    // ── Drop zone ──────────────────────────────────────────────────────────
    const fileInput  = document.getElementById('updateFile');
    const dropZone   = document.getElementById('dropZone');
    const dzFile     = document.getElementById('dzFile');
    const submitBtn  = document.getElementById('submitBtn');

    function setFile(file) {
        if (!file) return;
        const mb = (file.size / 1024 / 1024).toFixed(2);
        dzFile.textContent = file.name + ' (' + mb + ' MB)';
        dzFile.style.display = 'block';
        submitBtn.disabled = false;
    }

    fileInput.addEventListener('change', () => setFile(fileInput.files[0]));

    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag');
        const file = e.dataTransfer.files[0];
        if (file && file.name.endsWith('.zip')) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            setFile(file);
        } else {
            showStatus('{{ translate("Please drop a valid .zip file.") }}', 'fail');
        }
    });

    // ── Upload & apply ────────────────────────────────────────────────────
    const form        = document.getElementById('updateForm');
    const progressWrap = document.getElementById('progressWrap');
    const progressBar  = document.getElementById('progressBar');
    const progressPct  = document.getElementById('progressPct');
    const statusMsg    = document.getElementById('statusMsg');

    function showStatus(msg, type) {
        statusMsg.className = 'upd-status ' + type;
        statusMsg.innerHTML = msg;
        statusMsg.style.display = 'block';
    }
    function setProgress(pct) {
        progressBar.style.width = pct + '%';
        progressPct.textContent = pct + '%';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!fileInput.files.length) return;

        submitBtn.disabled = true;
        progressWrap.style.display = 'block';
        statusMsg.style.display    = 'none';
        progressBar.className      = 'upd-bar';
        setProgress(0);

        const xhr  = new XMLHttpRequest();
        const data = new FormData(form);

        xhr.upload.addEventListener('progress', ev => {
            if (ev.lengthComputable) {
                const pct = Math.round(ev.loaded / ev.total * 100);
                setProgress(pct);
                if (pct === 100) {
                    progressPct.textContent = '{{ translate("Processing...") }}';
                }
            }
        });

        xhr.addEventListener('load', function () {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    progressBar.classList.add('done');
                    setProgress(100);
                    showStatus('<i class="las la-check-circle"></i> ' + res.message, 'ok');
                    setTimeout(() => location.reload(), 2500);
                } else {
                    progressBar.classList.add('fail');
                    showStatus('<i class="las la-exclamation-circle"></i> ' + res.message, 'fail');
                    submitBtn.disabled = false;
                }
            } catch (_) {
                progressBar.classList.add('fail');
                showStatus('{{ translate("Unexpected server response. Check server logs.") }}', 'fail');
                submitBtn.disabled = false;
            }
        });

        xhr.addEventListener('error', () => {
            progressBar.classList.add('fail');
            showStatus('{{ translate("Network error. Check your connection.") }}', 'fail');
            submitBtn.disabled = false;
        });

        xhr.open('POST', '{{ route("admin.system.update") }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN',      '{{ csrf_token() }}');
        xhr.setRequestHeader('X-Requested-With',  'XMLHttpRequest');
        xhr.send(data);
    });

    // ── Click & Update ────────────────────────────────────────────────────
    const checkBtn    = document.getElementById('checkBtn');
    const checkStatus = document.getElementById('checkStatus');
    const updateList  = document.getElementById('updateList');

    function showCheckStatus(msg, type) {
        checkStatus.className = 'upd-status ' + type;
        checkStatus.innerHTML = msg;
        checkStatus.style.display = 'block';
    }

    checkBtn.addEventListener('click', function () {
        checkBtn.disabled = true;
        showCheckStatus('<i class="las la-spinner la-spin"></i> {{ translate("Checking...") }}', 'info');
        updateList.innerHTML = '';

        fetch('{{ route("admin.system.check.update") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            checkBtn.disabled = false;
            if (res.success && res.data && res.data.length) {
                showCheckStatus(res.data.length + ' {{ translate("update(s) available") }}', 'ok');
                res.data.forEach(upd => {
                    const card = document.createElement('div');
                    card.className = 'upd-card mb-2 d-flex justify-content-between align-items-center';
                    card.innerHTML =
                        '<div><b>v' + upd.version + '</b> <span class="text-muted small ms-2">' + (upd.release_date || '') + '</span></div>' +
                        '<button class="upd-btn upd-btn-primary install-btn" data-version="' + upd.version + '">' +
                        '<i class="las la-download"></i> {{ translate("Install") }}</button>';
                    updateList.appendChild(card);
                });
                document.querySelectorAll('.install-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        installVersion(this.dataset.version, this);
                    });
                });
            } else {
                showCheckStatus('{{ translate("Your software is up to date.") }}', 'ok');
            }
        })
        .catch(() => {
            checkBtn.disabled = false;
            showCheckStatus('{{ translate("Could not reach the update server.") }}', 'fail');
        });
    });

    function installVersion(version, btn) {
        btn.disabled = true;
        showCheckStatus('<i class="las la-spinner la-spin"></i> {{ translate("Installing") }} v' + version + '...', 'info');

        fetch('{{ route("admin.system.install.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type':     'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN':     '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: '_token={{ csrf_token() }}&version=' + version,
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showCheckStatus('<i class="las la-check-circle"></i> {{ translate("Installed! Reloading...") }}', 'ok');
                setTimeout(() => location.reload(), 2000);
            } else {
                showCheckStatus('<i class="las la-exclamation-circle"></i> ' + res.message, 'fail');
                btn.disabled = false;
            }
        })
        .catch(() => {
            showCheckStatus('{{ translate("Installation failed. Check server logs.") }}', 'fail');
            btn.disabled = false;
        });
    }
})();
</script>
@endpush
