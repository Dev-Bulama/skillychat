@extends('admin.layouts.master')


@section('content')
<div class="row g-4">
    <div class="col-xl-8">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title">
                    {{translate('Global Template')}}
                </h4>
            </div>
            <div class="card-body">
                <form action="{{route('admin.template.global.update')}}" enctype="multipart/form-data" method="post">
                    @csrf
                    <div class="form-inner">
                        <label for="body">
                            {{translate('Email Body')}} <small class="text-danger">*</small>
                        </label>
                        <textarea class="summernote " name="site_settings[default_mail_template]" id="body" cols="30" rows="10">@php echo (site_settings("default_mail_template")) @endphp</textarea>
                    </div>
                    <div class="form-inner">
                        <label for="smsBody">
                            {{translate('SMS Body')}} <small class="text-danger">*</small>
                        </label>
                        <textarea class="form-style" name="site_settings[default_sms_template]" id="smsBody" cols="30" rows="10">@php echo (site_settings("default_sms_template"))@endphp</textarea>
                    </div>
                    <div>
                        <button type="submit" class="i-btn btn--md btn--primary" data-anim="ripple">
                            {{translate("Submit")}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title">
                    {{translate('Template Key')}}
                </h4>
            </div>
            <div class="card-body">
                <div class="text-center d-flex gap-3 flex-column p-3 bg--primary-light rounded-1">
                    @foreach(Arr::get(config('settings'),"default_template_code" ,[]) as $key => $value)
                    <div class="d-flex  align-items-center justify-content-between">
                        <div class="me-2 ">
                            <p>{{ucfirst($value)}}</p>
                        </div>
                        <p class="mb-0">@php echo ("{{". $key ."}}") @endphp</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




@include('partials.tinymce_editor', [
    'selector' => '.tinymce-editor',
    'height' => 400
])

@push('script-push')
<script nonce="{{ csp_nonce() }}">
    (function($) {
        "use strict";

        $(document).ready(function() {


            


        });
    })(jQuery);

</script>
@endpush
