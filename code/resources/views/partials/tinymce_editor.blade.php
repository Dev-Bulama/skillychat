{{--
    TinyMCE Rich Text Editor Component

    Usage:
    @include('partials.tinymce_editor', [
        'selector' => '#editor',
        'height' => 400,
        'plugins' => ['link', 'lists', 'code'],
        'toolbar' => 'undo redo | bold italic | link | code'
    ])
--}}

@push('style-include')
<style nonce="{{csp_nonce()}}">
    .tox-tinymce {
        border: 1px solid #e1e8ed !important;
        border-radius: 8px !important;
        overflow: hidden;
    }
    .tox .tox-toolbar,
    .tox .tox-toolbar__overflow,
    .tox .tox-toolbar__primary {
        background: #f8f9fa !important;
        border-bottom: 1px solid #e1e8ed !important;
    }
    .tox .tox-edit-area__iframe {
        background: #fff !important;
    }
    .tox .tox-statusbar {
        background: #f8f9fa !important;
        border-top: 1px solid #e1e8ed !important;
    }
</style>
@endpush

@push('script-include')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin" nonce="{{csp_nonce()}}"></script>
@endpush

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function() {
    'use strict';

    const editorConfig = {
        selector: '{{ $selector ?? ".tinymce-editor" }}',
        height: {{ $height ?? 400 }},
        menubar: {{ isset($menubar) ? ($menubar ? 'true' : 'false') : 'true' }},

        plugins: [
            @if(isset($plugins))
                {!! '"' . implode('", "', $plugins) . '"' !!}
            @else
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            @endif
        ],

        toolbar: '{{ $toolbar ?? "undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code fullscreen | help" }}',

        @if(isset($toolbar_mode))
        toolbar_mode: '{{ $toolbar_mode }}',
        @endif

        content_style: `
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                font-size: 14px;
                line-height: 1.6;
                color: #333;
                padding: 10px;
            }
            p { margin: 0 0 10px; }
            h1, h2, h3, h4, h5, h6 { margin: 15px 0 10px; font-weight: 600; }
            code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
            pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        `,

        @if(isset($images_upload_url))
        images_upload_url: '{{ $images_upload_url }}',
        automatic_uploads: true,
        @endif

        @if(isset($file_picker_callback))
        file_picker_types: 'image',
        file_picker_callback: {{ $file_picker_callback }},
        @endif

        branding: false,
        promotion: false,

        setup: function(editor) {
            editor.on('init', function() {
                console.log('[TinyMCE] Editor initialized');
            });

            @if(isset($setup_callback))
            {{ $setup_callback }}(editor);
            @endif
        },

        @if(isset($init_callback))
        init_instance_callback: {{ $init_callback }},
        @endif

        // Responsive font sizes
        font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',

        // Enhanced code editing
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'Python', value: 'python' },
            { text: 'Java', value: 'java' },
            { text: 'C', value: 'c' },
            { text: 'C#', value: 'csharp' },
            { text: 'C++', value: 'cpp' }
        ],

        // Link options
        link_default_target: '_blank',
        link_class_list: [
            { title: 'None', value: '' },
            { title: 'Button Primary', value: 'btn btn-primary' },
            { title: 'Button Secondary', value: 'btn btn-secondary' }
        ],

        // Image options
        image_advtab: true,
        image_title: true,
        image_class_list: [
            { title: 'None', value: '' },
            { title: 'Responsive', value: 'img-fluid' },
            { title: 'Rounded', value: 'rounded' },
            { title: 'Thumbnail', value: 'img-thumbnail' }
        ],

        @if(isset($additional_config))
        ...{{ $additional_config }},
        @endif
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init(editorConfig);
        });
    } else {
        tinymce.init(editorConfig);
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (tinymce) {
            tinymce.remove();
        }
    });
})();
</script>
@endpush
