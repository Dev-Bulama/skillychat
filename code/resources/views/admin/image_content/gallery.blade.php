@extends('admin.layouts.master')
@section('content')

    <div class="i-card-md mt-4">

        <div class="card-body">
            <div class="search-action-area">
                <div class="row g-3">

                    <div class="col-md-6 d-flex justify-content-start gap-2">
                        <h4 class="card-title">
                            {{translate("Gallery Images")}}
                        </h4>
                    </div>
                    <div class="col-md-6 d-flex justify-content-between">
                        <div class="search-area">
                            <form action="{{route(Route::currentRouteName())}}" method="get">

                                <a href="javascript:void(0);" class="upload btn btn-sm btn-success" >
                                    <i class="bi bi-cloud-arrow-up-fill"></i>
                                    {{translate('Upload')}}
                                </a>

                                <div class="form-inner">
                                    <input name="search" value="{{request()->input('search')}}" type="search" placeholder="{{translate('Search by name ')}}">
                                </div>
                                <button class="i-btn btn--sm info">
                                    <i class="las la-sliders-h"></i>
                                </button>
                                <a href="{{route(Route::currentRouteName())}}"  class="i-btn btn--sm danger">
                                    <i class="las la-sync"></i>
                                </a>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

            <div class="grid-container position-relative">
                @include('admin.partials.loader')

                <div class="row">
                    @forelse ($contents as $content)
                        @include('partials.prompt_image_gallery')
                    @empty
                        <div class="col-12">
                            @include('admin.partials.not_found')
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="Paginations">
                {{ $contents->links() }}
            </div>
        </div>
    </div>
@endsection

@section('modal')
    @include('modal.delete_modal')
    @include('modal.bulk_modal')

    <div class="modal fade" id="content-form" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="content-form"   aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{translate('Add Content')}}
                    </h5>
                    <button class="close-btn" data-bs-dismiss="modal">
                        <i class="las la-times"></i>
                    </button>
                </div>

                <form action="{{route('admin.content.store')}}" id="contentForm" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <input disabled  hidden name="id" type="text">
                            <div class="col-lg-12">
                                <div class="form-inner">
                                    <label for="name" class="form-label" >
                                        {{translate('Content name')}} <small class="text-danger">*</small>
                                    </label>
                                    <input required type="text" placeholder="{{translate('Name')}}" id="name" name="name" value="{{old('name')}}">
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
	(function($){
       	"use strict";

        $(".select2").select2({
            placeholder:"{{translate('Select Item')}}",
        })

        $(".selectTemplate").select2({
            placeholder:"{{translate('Select Template')}}",
        })
        $(".sub_category_id").select2({
            placeholder:"{{translate('Select Sub Category')}}",
        })
        $(document).on('click','.create',function(e){
            e.preventDefault()
            $('.ai-section').fadeToggle(1000).toggleClass('d-none');;
        });
	})(jQuery);
</script>
@endpush
