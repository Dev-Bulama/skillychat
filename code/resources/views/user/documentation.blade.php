@extends('layouts.master')
@section('content')

<div class="i-card-md">
    <div class="card-header">
        <h4 class="card-title">{{ $title }}</h4>
    </div>
    <div class="card-body">
        @if($content)
            <div class="docs-content" style="line-height:1.8; font-size:.95rem;">
                {!! $content !!}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-book fs-2 d-block mb-2"></i>
                <p>{{ translate('No documentation available yet. Please check back later.') }}</p>
            </div>
        @endif
    </div>
</div>

@endsection
