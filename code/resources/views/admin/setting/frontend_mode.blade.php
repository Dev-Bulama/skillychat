@extends('admin.layouts.master')

@push('style-include')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" nonce="{{ csp_nonce() }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/material-darker.min.css" nonce="{{ csp_nonce() }}">
<style nonce="{{ csp_nonce() }}">
.CodeMirror {
    height: 520px;
    font-size: 13px;
    border-radius: 0 0 8px 8px;
    font-family: 'Fira Mono', 'Courier New', monospace;
}
.editor-toolbar {
    background: #263238;
    padding: 8px 12px;
    border-radius: 8px 8px 0 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.editor-toolbar span { width:12px;height:12px;border-radius:50%;display:inline-block; }
.mode-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 18px;
    cursor: pointer;
    transition: all .2s;
    margin-bottom: 12px;
}
.mode-card:hover { border-color: #6c63ff; }
.mode-card.selected { border-color: #6c63ff; background: #f0f0ff; }
.mode-card input[type=radio] { display:none; }
</style>
@endpush

@section('content')
<form action="{{ route('admin.setting.frontend.mode.save') }}" method="post" id="frontendModeForm">
    @csrf

    <div class="row g-4">

        {{-- Left: Mode selector --}}
        <div class="col-xl-4">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Frontend Mode') }}</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-14 mb-3">
                        {{ translate('Choose what visitors see when they open your website root URL.') }}
                    </p>

                    <label class="mode-card {{ $current_mode === 'default' ? 'selected' : '' }}" id="card-default">
                        <input type="radio" name="frontend_mode" value="default" {{ $current_mode === 'default' ? 'checked' : '' }}>
                        <div class="d-flex align-items-center gap-3">
                            <span class="icon-btn icon-btn-sm primary circle"><i class="bi bi-layout-text-window"></i></span>
                            <div>
                                <strong class="d-block">{{ translate('Default CMS') }}</strong>
                                <small class="text-muted">{{ translate('Show the standard CMS-managed homepage.') }}</small>
                            </div>
                        </div>
                    </label>

                    <label class="mode-card {{ $current_mode === 'custom' ? 'selected' : '' }}" id="card-custom">
                        <input type="radio" name="frontend_mode" value="custom" {{ $current_mode === 'custom' ? 'checked' : '' }}>
                        <div class="d-flex align-items-center gap-3">
                            <span class="icon-btn icon-btn-sm success circle"><i class="bi bi-code-slash"></i></span>
                            <div>
                                <strong class="d-block">{{ translate('Custom Code') }}</strong>
                                <small class="text-muted">{{ translate('Render your own HTML/CSS/Tailwind page.') }}</small>
                            </div>
                        </div>
                    </label>

                    <label class="mode-card {{ $current_mode === 'disabled' ? 'selected' : '' }}" id="card-disabled">
                        <input type="radio" name="frontend_mode" value="disabled" {{ $current_mode === 'disabled' ? 'checked' : '' }}>
                        <div class="d-flex align-items-center gap-3">
                            <span class="icon-btn icon-btn-sm danger circle"><i class="bi bi-lock"></i></span>
                            <div>
                                <strong class="d-block">{{ translate('Disabled (Login Redirect)') }}</strong>
                                <small class="text-muted">{{ translate('Visitors are redirected to the login page. /admin still works.') }}</small>
                            </div>
                        </div>
                    </label>

                    <div class="mt-4">
                        <button type="submit" class="i-btn btn--md btn--primary w-100" data-anim="ripple">
                            <i class="bi bi-save me-2"></i>{{ translate('Save Mode') }}
                        </button>
                    </div>

                    <div class="mt-3 p-3 border rounded fs-13 text-muted" style="background:#f8f9fa;">
                        <strong class="d-block mb-2">{{ translate('Current:') }}
                            <span class="badge {{ $current_mode === 'default' ? 'bg-primary' : ($current_mode === 'custom' ? 'bg-success' : 'bg-danger') }} ms-1">
                                {{ ucfirst($current_mode) }}
                            </span>
                        </strong>
                        @if($current_mode === 'default')
                            {{ translate('CMS homepage is shown to visitors.') }}
                        @elseif($current_mode === 'custom')
                            {{ translate('Your custom HTML is rendered at the root URL.') }}
                        @else
                            {{ translate('All visitors are redirected to login. Admin access via /admin.') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Code editor --}}
        <div class="col-xl-8" id="customEditorSection" style="{{ $current_mode !== 'custom' ? 'opacity:.4;pointer-events:none;' : '' }}">
            <div class="i-card-md h-100">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Custom HTML Editor') }}</h4>
                    <div class="d-flex gap-2">
                        <a href="/" target="_blank" class="i-btn btn--sm btn--outline">
                            <i class="bi bi-eye me-1"></i>{{ translate('Preview') }}
                        </a>
                        <button type="button" class="i-btn btn--sm btn--secondary" id="insertTailwind">
                            <i class="bi bi-magic me-1"></i>{{ translate('Insert Tailwind CDN') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert fs-13 mb-3" style="background:#e8f5e9;border-left:4px solid #4caf50;padding:10px 14px;border-radius:6px;">
                        <i class="bi bi-lightbulb me-2 text-success"></i>
                        {{ translate('Write a full HTML page. You can use Tailwind CSS, Bootstrap, or any CDN library. The output is rendered exactly as typed.') }}
                    </div>

                    <div class="editor-toolbar">
                        <span style="background:#ff5f57;"></span>
                        <span style="background:#febc2e;"></span>
                        <span style="background:#28c840;"></span>
                        <span class="text-white fs-12 ms-2">HTML / CSS / Tailwind</span>
                    </div>
                    <textarea name="custom_frontend_html" id="codeEditor">{{ old('custom_frontend_html', $custom_html) }}</textarea>
                </div>
            </div>
        </div>

    </div>
</form>
@endsection

@push('script-include')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js" nonce="{{ csp_nonce() }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js" nonce="{{ csp_nonce() }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js" nonce="{{ csp_nonce() }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js" nonce="{{ csp_nonce() }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js" nonce="{{ csp_nonce() }}"></script>
@endpush

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    // Init CodeMirror
    var editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
        mode: 'htmlmixed',
        theme: 'material-darker',
        lineNumbers: true,
        lineWrapping: true,
        tabSize: 2,
        indentWithTabs: false,
        autoCloseTags: true,
        matchBrackets: true,
        extraKeys: { 'Ctrl-Space': 'autocomplete' }
    });

    // Sync CodeMirror → textarea before form submit
    document.getElementById('frontendModeForm').addEventListener('submit', function () {
        editor.save();
    });

    // Mode card selection highlight + enable/disable editor panel
    var editorSection = document.getElementById('customEditorSection');

    document.querySelectorAll('.mode-card').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            var radio = this.querySelector('input[type=radio]');
            radio.checked = true;

            var isCustom = radio.value === 'custom';
            editorSection.style.opacity        = isCustom ? '1' : '.4';
            editorSection.style.pointerEvents  = isCustom ? 'auto' : 'none';
            editor.refresh();
        });
    });

    // Insert Tailwind CDN shortcut
    document.getElementById('insertTailwind').addEventListener('click', function () {
        var snippet = '<script src="https://cdn.tailwindcss.com"><\/script>';
        var cur = editor.getCursor();
        editor.replaceRange(snippet + '\n', cur);
        editor.focus();
    });
})();
</script>
@endpush
