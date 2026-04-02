{{--
    Quill Rich Text Editor Component (drop-in TinyMCE replacement, no API key required)

    Usage:
    @include('partials.tinymce_editor', [
        'selector' => '#editor',
        'height'   => 400,
    ])
--}}

@push('style-include')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" nonce="{{csp_nonce()}}">
<style nonce="{{csp_nonce()}}">
    .quill-editor-wrap .ql-toolbar.ql-snow {
        border: 1px solid #e1e8ed;
        border-radius: 8px 8px 0 0;
        background: #f8f9fa;
    }
    .quill-editor-wrap .ql-container.ql-snow {
        border: 1px solid #e1e8ed;
        border-top: none;
        border-radius: 0 0 8px 8px;
        background: #fff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 14px;
        line-height: 1.6;
        color: #333;
    }
    .quill-editor-wrap .ql-editor {
        min-height: 200px;
    }
    .quill-editor-wrap .ql-editor p { margin: 0 0 10px; }
    .quill-editor-wrap .ql-editor code {
        background: #f4f4f4;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }
    .quill-editor-wrap .ql-editor pre {
        background: #f4f4f4;
        padding: 10px;
        border-radius: 5px;
        overflow-x: auto;
    }
</style>
@endpush

@push('script-include')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js" nonce="{{csp_nonce()}}"></script>
@endpush

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function () {
    'use strict';

    function initQuill(selector, height) {
        var textareas = document.querySelectorAll(selector);
        textareas.forEach(function (textarea) {
            // Wrap and hide the original textarea
            var wrap = document.createElement('div');
            wrap.className = 'quill-editor-wrap';

            var editorDiv = document.createElement('div');
            editorDiv.style.minHeight = (height || 300) + 'px';
            wrap.appendChild(editorDiv);

            textarea.parentNode.insertBefore(wrap, textarea);
            textarea.style.display = 'none';

            var quill = new Quill(editorDiv, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ color: [] }, { background: [] }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ align: [] }],
                        ['link', 'image'],
                        ['blockquote', 'code-block'],
                        ['clean']
                    ]
                }
            });

            // Load existing content
            if (textarea.value) {
                quill.root.innerHTML = textarea.value;
            }

            // Keep textarea in sync
            quill.on('text-change', function () {
                textarea.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
            });

            // Ensure sync on form submit
            var form = textarea.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    textarea.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
                });
            }
        });
    }

    function ready(fn) {
        if (document.readyState !== 'loading') { fn(); }
        else { document.addEventListener('DOMContentLoaded', fn); }
    }

    ready(function () {
        initQuill('{{ $selector ?? ".tinymce-editor" }}', {{ $height ?? 300 }});
    });
})();
</script>
@endpush
