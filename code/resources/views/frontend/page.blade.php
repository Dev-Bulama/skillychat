@extends('layouts.master')

@push('style-include')
<style nonce="{{csp_nonce()}}">
    .text-editor-content {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 16px;
        line-height: 1.7;
        color: #333;
    }
    .text-editor-content h1,
    .text-editor-content h2,
    .text-editor-content h3,
    .text-editor-content h4,
    .text-editor-content h5,
    .text-editor-content h6 {
        font-weight: 600;
        margin-top: 1.5em;
        margin-bottom: 0.75em;
        line-height: 1.3;
        color: #1a1a1a;
    }
    .text-editor-content h1 { font-size: 2.5em; }
    .text-editor-content h2 { font-size: 2em; }
    .text-editor-content h3 { font-size: 1.75em; }
    .text-editor-content h4 { font-size: 1.5em; }
    .text-editor-content h5 { font-size: 1.25em; }
    .text-editor-content h6 { font-size: 1.1em; }

    .text-editor-content p {
        margin-bottom: 1.2em;
    }

    .text-editor-content a {
        color: #4F46E5;
        text-decoration: underline;
    }
    .text-editor-content a:hover {
        color: #4338CA;
    }

    .text-editor-content ul,
    .text-editor-content ol {
        margin-bottom: 1.2em;
        padding-left: 2em;
    }
    .text-editor-content li {
        margin-bottom: 0.5em;
    }

    .text-editor-content blockquote {
        border-left: 4px solid #4F46E5;
        padding-left: 1.5em;
        margin: 1.5em 0;
        font-style: italic;
        color: #555;
    }

    .text-editor-content pre {
        background: #f4f4f4;
        padding: 1em;
        border-radius: 6px;
        overflow-x: auto;
        margin-bottom: 1.2em;
    }
    .text-editor-content code {
        background: #f4f4f4;
        padding: 0.2em 0.4em;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }
    .text-editor-content pre code {
        background: none;
        padding: 0;
    }

    .text-editor-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1em 0;
    }

    .text-editor-content table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.2em;
    }
    .text-editor-content table th,
    .text-editor-content table td {
        border: 1px solid #ddd;
        padding: 0.75em;
        text-align: left;
    }
    .text-editor-content table th {
        background: #f8f9fa;
        font-weight: 600;
    }

    .text-editor-content strong,
    .text-editor-content b {
        font-weight: 600;
    }

    .text-editor-content em,
    .text-editor-content i {
        font-style: italic;
    }
</style>
@endpush

@section('content')

@include("frontend.partials.breadcrumb")
  <section class="pages-wrapper pb-110">
    <div class="container">
      <div class="row">
          <div class="col-lg-12">
              <div class="page-content text-editor-content linear-bg">
                  {!! @$page->description !!}
              </div>
          </div>
      </div>
    </div>
  </section> 
@endsection

