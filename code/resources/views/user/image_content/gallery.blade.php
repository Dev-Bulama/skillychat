@extends('layouts.master')
@push('style-include')
    <link nonce="{{ csp_nonce() }}" href="{{asset('assets/global/css/datepicker/daterangepicker.css')}}" rel="stylesheet"
        type="text/css" />
@endpush
@section('content')

    @php
        $user = auth_user('web')->load(['runningSubscription']);
    @endphp


    <div class="mt-4">
        <div class="i-card-md">
            <div class="card-header">
                <h4 class="card-title">
                    {{translate(Arr::get($meta_data, 'title'))}}
                </h4>

                <div class="d-flex justify-content-end align-items-end gap-2">

                    <a href="javascript:void(0);" class="upload icon-btn icon-btn-lg success circle" >
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </a>

                    <button class="icon-btn icon-btn-lg info circle" type="button" data-bs-toggle="collapse"
                        data-bs-target="#tableFilter" aria-expanded="false" aria-controls="tableFilter">
                        <i class="bi bi-sliders"></i>
                    </button>
                </div>
            </div>

            <div class="collapse   {{ hasFilter(['search']) ? 'show' : '' }} " id="tableFilter">
                <div class="search-action-area pb-0">
                    <div class="search-area">
                        <form action="{{ route(Route::currentRouteName()) }}" method="get">

                            <div class="form-inner">
                                <input placeholder="{{translate('Search by name')}}" type="search" name="search"
                                    value="{{request()->input('search')}}">
                            </div>

                            <div class="d-flex gap-2">
                                <button class="i-btn primary btn--lg capsuled">
                                    <i class="bi bi-search"></i>
                                </button>
                                <a href="{{ route(Route::currentRouteName())}}" class="i-btn danger btn--lg capsuled">
                                    <i class="bi bi-arrow-repeat"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    @forelse ($contents as $content)
                        @include('user.partials.prompt_image_gallery')
                    @empty
                        <div class="col-12">
                            @include('admin.partials.not_found')
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
        <div class="Paginations">
            {{ $contents->links() }}
        </div>
    </div>

@endsection

@section('modal')

    @include('modal.delete_modal')

    <div class="modal fade" id="content-form" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="content-form" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{translate('Add Content')}}
                    </h5>
                    <button class="close-btn" data-bs-dismiss="modal">
                        <i class="las la-times"></i>
                    </button>
                </div>

                <form action="{{route('user.ai.content.store')}}" id="contentForm" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">

                            <input disabled hidden name="id" type="text">
                            <div class="col-lg-12">
                                <div class="form-inner">
                                    <label for="name" class="form-label">
                                        {{translate('Name')}} <small class="text-danger">*</small>
                                    </label>
                                    <input required type="text" placeholder="{{translate('Name')}}" id="name" name="name"
                                        value="{{old('name')}}">

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="i-btn btn--md ripple-dark" data-anim="ripple" data-bs-dismiss="modal">
                            {{translate("Close")}}
                        </button>
                        <button type="submit" class="i-btn btn--md btn--primary" data-anim="ripple">
                            {{translate("Submit")}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="upload-form" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ulpoad-form"   aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{translate('Upload Image Content')}}
                    </h5>
                    <button class="close-btn" data-bs-dismiss="modal">
                        <i class="las la-times"></i>
                    </button>
                </div>

                <form action="{{route('admin.content.image.upload')}}" id="uploadForm" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input disabled  hidden name="id" type="text">
                            <div class="col-lg-12">
                                <div class="form-inner">
                                    <label for="name" class="form-label" >
                                        {{translate('Image content')}} <small class="text-danger">*</small>
                                    </label>
                                    <input required type="file" placeholder="{{translate('Images')}}" id="images" name="images[]" multiple >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="i-btn btn--md ripple-dark" data-anim="ripple" data-bs-dismiss="modal">
                            {{translate("Close")}}
                        </button>
                        <button type="submit" class="i-btn btn--md btn--primary" data-anim="ripple">
                            {{translate("Submit")}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script-include')

    @include('partials.ai_content_script');

@endpush

@push('script-push')
    <script nonce="{{ csp_nonce() }}">
        (function ($) {
            "use strict";

            $(".select2").select2({
                placeholder: "{{translate('Select Item')}}",
            })
            $(".selectTemplate").select2({
                placeholder: "{{translate('Select Template')}}",
            })
            $(".sub_category_id").select2({
                placeholder: "{{translate('Select Sub Category')}}",
            })
        })(jQuery);
    </script>
@endpush
