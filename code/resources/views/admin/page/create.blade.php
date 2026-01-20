@extends('admin.layouts.master')

@section('content')
<form action="{{route('admin.page.store')}}" class="add-listing-form" enctype="multipart/form-data" method="post">
    @csrf
    <div class="row g-4">
        @php
        $col = @site_settings('site_seo') == App\Enums\StatusEnum::true->status() ? 8 :12;
        @endphp
        <div class="col-xl-{{$col}}">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">
                        {{translate('Basic Information')}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-inner">
                                <label class="form-label" for="title">
                                    {{translate('Title')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="title" id="title" placeholder='{{translate("Enter Title")}}' value='{{old("title")}}' required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-inner">
                                <label class="form-label" for="slug">
                                    {{translate('Slug')}}
                                </label>
                                <input type="text" name="slug" id="slug" placeholder='{{translate("Enter Slug")}}'
                                    value='{{old("slug")}}'>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-inner">
                                <label class="form-label" for="description">
                                    {{translate('Description')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea id="description" class="tinymce-editor" name="description" cols="30" rows="10">{{old("description")}}</textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-inner">
                                <label for="serial_id">
                                    {{translate('Serial Id')}} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="serial_id" value='{{old("serial_id") ? old("serial_id") : $serialId}}'>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="i-btn btn--md btn--primary" data-anim="ripple">
                                {{translate("Submit")}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @includeWhen(@site_settings('site_seo') == App\Enums\StatusEnum::true->status(),'admin.partials.seo')
    </div>
</form>
@endsection

@include('partials.tinymce_editor', [
    'selector' => '.tinymce-editor',
    'height' => 400
])

@push('script-push')
<script nonce="{{ csp_nonce() }}">
    (function($) {
        "use strict";
        $(".selectMeta").select2({
            placeholder: "{{translate('Enter Keywords')}}",
            tags: true,
            tokenSeparators: [',']
        })
    })(jQuery);
</script>
@endpush