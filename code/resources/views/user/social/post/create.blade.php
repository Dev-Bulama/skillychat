@php use Illuminate\Support\Arr; @endphp
@extends('layouts.master')
@section('content')

@push('style-include')
    <link nonce="{{ csp_nonce() }}"  href="{{asset('assets/frontend/css/post.css')}}" rel="stylesheet" type="text/css">
    <link nonce="{{ csp_nonce() }}" href="{{asset('assets/global/css/datepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css" />
    <style>

    /* AI Assistant Dropdown Enhancements */
    .compose-body-bottom .dropdown .action-item.image-dropdwon.dropdown-toggle {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        padding: 10px 16px;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .compose-body-bottom .dropdown .action-item.image-dropdwon.dropdown-toggle:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .compose-body-bottom .dropdown .action-item.image-dropdwon.dropdown-toggle:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        outline: none;
    }

    .compose-body-bottom .dropdown .action-item.image-dropdwon.dropdown-toggle::after {
        margin-left: 8px;
        transition: transform 0.3s ease;
    }

    .compose-body-bottom .dropdown .action-item.image-dropdwon.dropdown-toggle[aria-expanded="true"]::after {
        transform: rotate(180deg);
    }


    /* Dropdown Menu Styling */
    .compose-body-bottom .dropdown .dropdown-menu {

        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        background: white;
        padding: 8px !important;
        margin-top: 8px;
        animation: dropdownFadeIn 0.2s ease-out;
        min-width : 200px !important;
    }

    @keyframes dropdownFadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Dropdown Items */
    .compose-body-bottom .dropdown .dropdown-menu li {
        margin-bottom: 4px;
    }

    .compose-body-bottom .dropdown .dropdown-menu li:last-child {
        margin-bottom: 0;
    }

    .compose-body-bottom .dropdown .dropdown-menu .ai-modal,
    .compose-body-bottom .dropdown .dropdown-menu .ai-image-modal,
    .compose-body-bottom .dropdown .dropdown-menu .ai-video-modal,
    .compose-body-bottom .dropdown .dropdown-menu .ai-image-gallery-modal,
    .compose-body-bottom .dropdown .dropdown-menu .ai-video-gallery-modal {
        border-radius: 8px;
        transition: all 0.2s ease;
        padding: 12px 16px !important;
        margin: 0;
        background: transparent;
        border: 1px solid transparent;
    }

    .compose-body-bottom .dropdown .dropdown-menu .ai-modal:hover,
    .compose-body-bottom .dropdown .dropdown-menu .ai-image-modal:hover,
    .compose-body-bottom .dropdown .dropdown-menu .ai-video-modal:hover,
    .compose-body-bottom .dropdown .dropdown-menu .ai-image-gallery-modal:hover,
    .compose-body-bottom .dropdown .dropdown-menu .ai-video-gallery-modal:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #e8ecff 100%);
        border-color: #e0e7ff;
        transform: translateX(4px);
    }

    /* Icons Styling */
    .compose-body-bottom .dropdown .dropdown-menu .bi {
        font-size: 18px;
        color: #667eea;
        width: 20px;
        text-align: center;
    }

    .compose-body-bottom .dropdown .dropdown-menu .bi-robot {
        color: #667eea;
    }

    .compose-body-bottom .dropdown .dropdown-menu .bi-images {
        color: #f59e0b;
    }

    .compose-body-bottom .dropdown .dropdown-menu .bi-film {
        color: #ef4444;
    }

    /* Text Styling */
    .compose-body-bottom .dropdown .dropdown-menu p {
        margin: 0;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
        line-height: 1.4;
    }

    .compose-body-bottom .dropdown .dropdown-menu .ai-modal:hover p,
    .compose-body-bottom .dropdown .dropdown-menu .ai-image-modal:hover p,
    .compose-body-bottom .dropdown .dropdown-menu .ai-video-modal:hover p,
    .compose-body-bottom .dropdown .dropdown-menu .ai-image-gallery-modal:hover p,
    .compose-body-bottom .dropdown .dropdown-menu .ai-video-gallery-modal:hover p {
        color: #1f2937;
    }

    /* Active/Focus States */
    .compose-body-bottom .dropdown .dropdown-menu .ai-modal:active,
    .compose-body-bottom .dropdown .dropdown-menu .ai-image-modal:active,
    .compose-body-bottom .dropdown .dropdown-menu .ai-video-modal:active,
    .compose-body-bottom .dropdown .dropdown-menu .ai-image-gallery-modal:active,
    .compose-body-bottom .dropdown .dropdown-menu .ai-video-gallery-modal:active {
        transform: translateX(2px);
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .compose-body-bottom .dropdown .dropdown-menu {
            min-width: 220px !important;
        }

        .compose-body-bottom .dropdown .action-item.image-dropdwon.dropdown-toggle {
            padding: 8px 12px;
            font-size: 14px;
        }
    }

    </style>
@endpush

@section('content')
@php
    $user = auth_user('web')->load(['runningSubscription','runningSubscription.package']);
    $schedule = false;

    $notes = trans('default.platform_notes') ;

    if($user->runningSubscription){
        $package = $user->runningSubscription->package;
        if($package && @$package->social_access->schedule_post == App\Enums\StatusEnum::true->status()) $schedule = true;
    }
@endphp

<div class="compose-wrapper">
    <form action="{{route('user.social.post.store')}}" method="post" class="compose-form" enctype="multipart/form-data">
        @csrf
        <div class="row g-4">
            <div class="col-xxl-8 col-lg-7">
                <div class="i-card-md">
                    <div class="card-body">
                        <div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <h4 class="card-title mb-3">{{translate('Where to post')}}</h4>
                                </div>

                                @if($platforms->count() > 0)
                                    <div class="row gy-3">
                                        <div class="col-xl-6 col-lg-12 col-md-6">
                                            <ul class="nav nav-tabs post-select-tab" id="postTypeTab" role="tablist">
                                                @foreach($platforms as  $platform)
                                                    <li class="nav-item" role="presentation">
                                                        <button  class="nav-link  {{ $loop->index == 0 ? 'active' : '' }} " id="{{$platform->slug}}-tab" data-bs-toggle="tab" data-bs-target="#{{$platform->slug}}-tab-pane" type="button" role="tab" aria-controls="{{$platform->slug}}-tab-pane" aria-selected="true">
                                                            <img src="{{imageURL(@$platform->file,'platform',true)}}" alt="{{@$platform->name .translate( 'Feature image')}}">
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        <div class="col-xl-6 col-lg-12 col-md-6 d-flex
                                                    justify-content-xl-end
                                                    justify-content-lg-start
                                                    justify-content-md-end
                                                    justify-content-start">
                                            <div class="tab-content" id="postTypeTabContent">
                                                @foreach ($platforms as  $platform)
                                                    @php

                                                        $postTypes = App\Enums\PostType::toArray();
                                                        if($platform->slug == 'facebook') $postTypes =  Arr::except( $postTypes,[App\Enums\PostType::STORY->name]);
                                                        if($platform->slug == 'twitter') $postTypes  =  Arr::except( $postTypes,[App\Enums\PostType::REELS->name,App\Enums\PostType::STORY->name]);
                                                        if($platform->slug == 'linkedin') $postTypes =  Arr::except( $postTypes,[App\Enums\PostType::REELS->name,App\Enums\PostType::STORY->name]);
                                                        if($platform->slug == 'tiktok')   $postTypes   =  Arr::except( $postTypes,[App\Enums\PostType::REELS->name,App\Enums\PostType::STORY->name]);

                                                    @endphp

                                                    <div class="tab-pane fade  {{ $loop->index == 0 ? 'show active' : '' }}" id="{{$platform->slug}}-tab-pane" role="tabpanel" aria-labelledby="{{$platform->slug}}-tab" tabindex="0">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            @foreach ($postTypes as  $type => $value)
                                                                <div class="radio--button">
                                                                    <input {{ $loop->index == 0 ? 'checked' : ''}}  type="radio" id="post_type_{{$platform->slug}}-{{$loop->index}}"  name="post_type[{{$platform->slug}}]" value="{{$value}}" />
                                                                    <label for="post_type_{{$platform->slug}}-{{$loop->index}}"> {{$type}}</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <div class="d-flex justify-content-between">
                                        <h4 class="card-title mb-3">{{translate('Choose Profile')}}</h4>
                                    </div>

                                    <select name="account_id[]" multiple="multiple" class="w-100 profile-select" >
                                        @foreach (@$accounts as $account )
                                            @php
                                                $imgUrl = isValidImageUrl(@$account->account_information->avatar)
                                                                ? @$account->account_information->avatar
                                                                : route('default.image', '200x200')
                                            @endphp

                                            <option @if(old('account_id') && is_array(old('account_id')) && in_array($account->id , old('account_id')))  selected @endif    value="{{ $account ->id }}" data-image="{{ $imgUrl}}">{{$account->name}} - {{ @$account->platform->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h4 class="card-title mb-3">
                                    {{translate('Create your post')}}
                                </h4>

                                <div class="caption-wrapper">
                                    <div class="form-inner mb-0">
                                        <div class="compose-body">
                                            <textarea name="text" cols="30" rows="4"
                                                placeholder="{{translate('Start Writing')}}" class="compose-input post-intput"
                                                id="inputText" contenteditable="true">{{old('text')}}</textarea>

                                            <div class="compose-body-bottom">
                                                <div class="caption-action d-flex gap-3 justify-content-start">


                                                     <div class="dropdown">
                                                        <button class="action-item image-dropdwon dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                            {{translate("AI Assistant")}}
                                                        </button>

                                                        <ul class="dropdown-menu ai-dropdown p-2">

                                                            <li>

                                                                <div
                                                                    class="ai-modal d-flex align-items-center gap-2 cursor-pointer px-2 py-1">
                                                                    <i class="bi bi-card-text"></i>
                                                                    <p>
                                                                        {{translate("Generate Text")}}
                                                                    </p>
                                                                </div>
                                                            </li>
                                                            <li>

                                                                <div
                                                                    class="ai-image-modal d-flex align-items-center gap-2 cursor-pointer px-2 py-1">
                                                                    <i class="bi bi-image-alt"></i>
                                                                    <p>
                                                                        {{translate("Generate Image")}}
                                                                    </p>
                                                                </div>
                                                            </li>

                                                            <li>

                                                                <div
                                                                    class="ai-video-modal d-flex align-items-center gap-2 cursor-pointer px-2 py-1">
                                                                    <i class="bi bi-camera-reels"></i>
                                                                    <p>
                                                                        {{translate("Generate Video")}}
                                                                    </p>
                                                                </div>
                                                            </li>



                                                            <li>

                                                                <div
                                                                    class="ai-image-gallery-modal d-flex align-items-center gap-2 cursor-pointer px-2 py-1">
                                                                    <i class="bi bi-images"></i>
                                                                    <p>
                                                                        {{translate("Import Image")}}
                                                                    </p>
                                                                </div>
                                                            </li>

                                                            <li>

                                                                <div
                                                                    class="ai-video-gallery-modal d-flex align-items-center gap-2 cursor-pointer px-2 py-1">
                                                                    <i class="bi bi-film"></i>
                                                                    <p>
                                                                        {{translate("Import Video")}}
                                                                    </p>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                    <div class="upload-filed">
                                                        <input id="media-file" multiple type="file" name="files[]">
                                                        <label for="media-file" class="d-flex align-items-center gap-2 cursor-pointer px-2 py-1">
                                                            <span class="d-flex align-items-center flex-row gap-2">
                                                                <span class="upload-drop-file">
                                                                    <i class="bi bi-image fs-20"></i>
                                                                </span>
                                                                <span>
                                                                    {{translate('Photo/Video')}}
                                                                </span>
                                                            </span>
                                                        </label>
                                                    </div>

                                                    <div>
                                                        <select class="form-select predefined-select" aria-label="Default select example" id="predefined">
                                                            <option value="">{{translate("Predefined Content")}}</option>
                                                            @foreach($contents as  $content)
                                                                <option value="{{$content->content}}">
                                                                    {{$content->name}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    @if($schedule)
                                                        <div class="schedule-btn">
                                                            <div class="px-3 custom-date-label" id="schedule_date_picker" data-bs-toggle="tooltip"
                                                                data-bs-title="{{translate('Schedule Post')}}">
                                                                <i class="bi bi-clock"></i>
                                                            </div>
                                                            <p class="show-date"></p>
                                                        </div>
                                                       <input type="datetime-local"  hidden name="schedule_date" id="schedule_date_input" >

                                                    @endif
                                                </div>
                                                <ul class="file-list mt-3"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="mb-4">
                                <h4 class="card-title mb-3">{{translate('Links')}}</h4>
                                <div class="input-group mb-0">
                                    <input type="text" placeholder="{{translate('Enter link')}}" name="link" id="link" value="{{old('link')}}" class="form-control" />
                                </div>
                            </div>

                            <button  type="submit"
                                class="i-btn btn--primary btn--lg capsuled postSubmitButton"
                                id="postSubmitButton">
                                {{translate("Post")}}
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-lg-5">
                <div class="i-card-md social-preview-user">
                    <div class="card-header">
                        <h4 class="card-title">
                            {{translate("Suggestion/Preview")}}
                        </h4>
                    </div>
                    <div class="card-body">

                        <div class="d-flex flex-column gap-4">
                            @foreach ($platforms as  $platform)


                                  @php
                                      $note = Arr::get($notes , $platform->slug);
                                  @endphp

                                    <div class="flip-wrapper">

                                        <div class=" platform-note post-before-social-card d-flex justify-content-start gap-3 align-items-start">
                                            <div class="icon facebook">
                                                <i class="bi bi-{{$platform->slug}}"></i>
                                            </div>
                                            <div class="content">
                                                <h5 class="mb-3">
                                                    {{
                                                        k2t($platform->slug)
                                                    }}
                                                </h5>
                                                <p>{{$note}}</p>
                                            </div>
                                        </div>

                                        <div class="social-preview-body fade-in  d-none {{$platform->slug }}">

                                            <div class="post-logo">
                                                <img data-bs-toggle="tooltip" data-bs-title="{{$platform->name . translate(' Preview')}}" src="{{e(imageURL(@$platform->file,'platform',true))}}" alt="{{@$platform->name .translate( 'Feature image')}}">
                                            </div>

                                            <div class="social-auth">
                                                <div class="profile-img">
                                                    <img src="{{e(get_default_img())}}"
                                                        alt="{{translate('Fallback default image')}}" />
                                                </div>

                                                <div class="profile-meta">
                                                    <h6 class="user-name">

                                                            {{translate('Username')}}

                                                    </h6>
                                                    @if($platform->slug == 'facebook')
                                                        <div class="d-flex align-items-center gap-2">
                                                            <p>
                                                            {{Carbon\Carbon::now()->format('F j')}}
                                                            </p>
                                                            <i class="bi bi-globe-americas fs-12"></i>
                                                        </div>
                                                    @else
                                                        <p>
                                                           {{Carbon\Carbon::now()->format('F j')}}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="social-caption">
                                                <div class="caption-text">
                                                </div>
                                                <div class="caption-imgs position-relative">
                                                    <div class="caption-img caption-placeholder">
                                                        <img class="w-100 h-100" src="{{get_default_img()}}" alt="Default Image">
                                                    </div>
                                                </div>
                                                <div class="caption-link"></div>


                                                <div class="caption-action">
                                                    @if($platform->slug == 'facebook')
                                                        <div class="caption-action-item">
                                                            <i class="bi bi-hand-thumbs-up"></i>
                                                            <span>{{translate('Like')}}</span>
                                                        </div>

                                                        <div class="caption-action-item">
                                                            <i class="bi bi-chat-right"></i>
                                                            <span>{{translate('Comment')}}</span>
                                                        </div>

                                                        <div class="caption-action-item">
                                                            <i class="bi bi-share"></i>
                                                            <span>{{translate('Share')}}</span>
                                                        </div>
                                                    @elseif($platform->slug == 'instagram')
                                                        <div class="caption-action-item">
                                                            <i class="bi bi-heart"></i>
                                                        </div>
                                                        <div class="caption-action-item">
                                                            <i class="bi bi-chat-right"></i>
                                                        </div>
                                                        <div class="caption-action-item">
                                                            <i class="bi bi-send"></i>
                                                        </div>
                                                    @elseif($platform->slug == 'twitter')
                                                        <div class="caption-action-item">
                                                            <i class="bi bi-chat-right"></i>
                                                        </div>
                                                        <div class="caption-action-item">
                                                            <i class="bi bi-repeat"></i>
                                                        </div>
                                                        <div class="caption-action-item">
                                                            <i class="bi bi-heart"></i>
                                                        </div>
                                                    @elseif($platform->slug == 'linkedin')
                                                        <div class="caption-action-item">
                                                            <i class="bi bi-hand-thumbs-up"></i>
                                                            <span>{{translate('Like')}}</span>
                                                        </div>

                                                        <div class="caption-action-item">
                                                            <i class="bi bi-chat-right"></i>
                                                            <span>{{translate('Comment')}}</span>
                                                        </div>

                                                        <div class="caption-action-item">
                                                            <i class="bi bi-repeat"></i>
                                                            <span>{{translate('Repost')}}</span>
                                                        </div>

                                                        <div class="caption-action-item">
                                                            <i class="bi bi-send"></i>
                                                            <span>{{translate('Send')}}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                            </div>
                                        </div>

                                    </div>



                            @endforeach
                        </div>

                        @if($platforms->count() == 0)
                           @include('admin.partials.not_found')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


@endsection

@section('modal')


<div class="modal fade" id="aiModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="aiModal">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title ai-modal-title">
                    {{translate('Generate Content')}}
                </h5>

                <button class="icon-btn icon-btn-sm danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="modal-body modal-body-section">
                @include('partials.prompt_content',['content_route' => route("user.ai.content.store"),'modal' => true])
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="aiImageModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="aiImageModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title ai-modal-title">
                  {{translate('Generate Image Content')}}
                </h5>
                <button class="icon-btn icon-btn-sm danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body modal-body-section">
                @include('partials.prompt_image_content',['content_route' => route("user.ai.content.image.store"),'modal' => true])
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="aiVideoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="aiImageModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title ai-modal-title">
                  {{translate('Generate Video Content')}}
                </h5>
                <button class="icon-btn icon-btn-sm danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body modal-body-section">
                @include('partials.prompt_video_content',['content_route' => route("user.ai.content.video.store"),'modal' => true])
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="aiImageGalleryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="aiVideoGalleryModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title ai-modal-title">
                  {{translate('Image gallery')}}
                </h5>
                <button class="icon-btn icon-btn-sm danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body modal-body-section">
                @include('partials.image_gallery')
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="aiVideoGalleryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="aiVideoGalleryModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title ai-modal-title">
                  {{translate('Video gallery')}}
                </h5>
                <button class="icon-btn icon-btn-sm danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body modal-body-section">
                @include('partials.video_gallery')
            </div>
        </div>
    </div>
</div>




@endsection

@push('script-include')
     @include('partials.ai_content_script');
    <script nonce="{{ csp_nonce() }}" src="{{asset('assets/global/js/post.js')}}"></script>
    <script nonce="{{ csp_nonce() }}" src="{{asset('assets/global/js/datepicker/moment.min.js')}}"></script>
    <script nonce="{{ csp_nonce() }}" src="{{asset('assets/global/js/datepicker/daterangepicker.min.js')}}"></script>
@endpush


@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function($) {
    "use strict";

    $(document).on('change', '#predefined', function(e) {
        e.preventDefault()
        var value = $(this).val();
        var cleanContent = DOMPurify.sanitize(value);
        $("#inputText").val(cleanContent)
        $(".caption-text").html(cleanContent)
        $('.platform-note').addClass('d-none');
        $('.social-preview-body').removeClass('d-none');

    })

    $(".user").select2({  placeholder:"{{translate('Select user')}}"})

    $(document).on('click', '.ai-modal', function(e) {
        e.preventDefault()
        var modal = $('#aiModal');
        modal.find('.ai-content-form')[0].reset();
        modal.find('.ai-content-div').addClass("d-none")
        modal.find('#ai-form').fadeIn()
        modal.find('.ai-modal-title').html("{{translate('Generate Content')}}")
        modal.modal('show');

    });

    function toggleContentPreview(inputText , modal){

        if (inputText.length > 0) {
            modal.find('#contentPreviewSection').removeClass('d-none');
            modal.find('#postPreview').val(inputText);
        } else {
            modal.find('#contentPreviewSection').addClass('d-none');
            modal.find('#postPreview').val('');
        }

    }

    $(document).on('click', '.ai-image-modal', function(e) {
        e.preventDefault()
        var modal       = $('#aiImageModal');
        var inputText   = $('#inputText').val();
        modal.find('.ai-content-image-form')[0].reset();
        toggleContentPreview(inputText , modal)
        modal.find('.ai-content-div').addClass("d-none")
        modal.find('#ai-image-form').fadeIn()
        modal.find('.ai-modal-title').html("{{translate('Generate Image Content')}}")
        modal.modal('show');
    });

    $(document).on('click', '.ai-video-modal', function(e) {
        e.preventDefault()
        var modal       = $('#aiVideoModal');
        var inputText   = $('#inputText').val();
        modal.find('.ai-content-video-form')[0].reset();
        toggleContentPreview(inputText , modal)
        modal.find('.ai-content-div').addClass("d-none")
        modal.find('#ai-video-form').fadeIn()
        modal.find('.ai-modal-title').html("{{translate('Generate Video Content')}}")
        modal.modal('show');
    });

    $(document).on('click', '.ai-image-gallery-modal', function(e) {
        e.preventDefault()
        var modal = $('#aiImageGalleryModal');
        modal.find('.ai-modal-title').html("{{translate('Image gallery')}}")
        modal.modal('show');
    });

    $(document).on('click', '.ai-video-gallery-modal', function(e) {
        e.preventDefault()
        var modal = $('#aiVideoGalleryModal');
        modal.find('.ai-modal-title').html("{{translate('Video gallery')}}")
        modal.modal('show');
    });


    $(".select2").select2({
        placeholder:"{{translate('Select Category')}}",
        dropdownParent: $("#aiModal"),
    })
    $(".language").select2({
        placeholder:"{{translate('Select Language')}}",
        dropdownParent: $("#aiModal"),
    })

    $(".selectTemplate").select2({
        placeholder:"{{translate('Select Template')}}",
        dropdownParent: $("#aiModal"),
    })
    $(".sub_category_id").select2({
        placeholder:"{{translate('Select Sub Category')}}",
        dropdownParent: $("#aiModal"),
    })

    $(document).on('click', '.copy-content', function(e) {
        e.preventDefault()
        var textarea = document.getElementById('content');
        textarea.select();
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
        toastr("{{translate('Text copied to clipboard!')}}", 'success');
    });


    $(document).on('click', '.insert-text', function(e) {

        e.preventDefault()
        var content = $('textarea#content').val();
        var cleanContent = DOMPurify.sanitize(content);
        $('.post-intput').val(cleanContent)
        var modal = $('#aiModal');
        modal.modal('hide');

        $(".caption-text").html(cleanContent);

        $('.platform-note').addClass('d-none');
        $('.social-preview-body').removeClass('d-none');



    });

    $(document).on('click', '.download-text', function(e) {
        e.preventDefault()
        var content = document.getElementById('content').value;
        var cleanContent = DOMPurify.sanitize(content);

        var blob = new Blob([cleanContent], {
            type: 'text/html'
        });
        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = 'downloaded_content.html';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    $(document).on('click', '.insert-image', function(e) {
        e.preventDefault();

        if ($(this).hasClass('disabled')) return;
        $(this).addClass('disabled');

        const modalName = $(this).data('modal');
        const $modal = $(`#${modalName}`);

        const imageUrls = $modal.find('.image-check:checked').map(function() {
            return $(this).val();
        }).get();

        if (!imageUrls || imageUrls.length === 0) {
            console.log('Error: No image URLs found');
            $(this).removeClass('disabled');
            return;
        }

        const $platformNote = $('.platform-note');
        const $socialPreviewBody = $('.social-preview-body');
        const $fileInput = $('#media-file');



        requestAnimationFrame(() => {
            $platformNote.addClass('d-none');
            $socialPreviewBody.removeClass('d-none');
        });

        $.ajax({
            url: '{{ route("process.images") }}',
            method: 'GET',
            data: {
                urls: imageUrls
            },
            beforeSend: function() {
                $('.ai-btn-insert').prop("disabled",true);
                $('.ai-btn-insert').html(`{{translate('Insert')}}`)
                $('.ai-btn-insert').html(`{{translate('Insert')}}<div class="spinner-border spinner-border-sm text-white" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>`)

            },
            success: function(response) {
                const files = response.map(function(item, index) {
                    if (item.error) {
                        console.error('Error for image:', item.url, item.error);
                        return null;
                    }

                    var binary = atob(item.content);
                    var array = new Uint8Array(binary.length);
                    for (var i = 0; i < binary.length; i++) {
                        array[i] = binary.charCodeAt(i);
                    }

                    var blob = new Blob([array], { type: item.content_type });

                    let fileExtension = 'jpg';
                    let mimeType = item.content_type || 'image/jpeg';

                    switch (item.content_type) {
                        case 'image/png':
                            fileExtension = 'png';
                            mimeType = 'image/png';
                            break;
                        case 'image/jpeg':
                        case 'image/jpg':
                            fileExtension = 'jpg';
                            mimeType = 'image/jpeg';
                            break;
                        case 'image/gif':
                            fileExtension = 'gif';
                            mimeType = 'image/gif';
                            break;
                        case 'image/webp':
                            fileExtension = 'webp';
                            mimeType = 'image/webp';
                            break;
                        default:
                            if (!item.content_type) {
                                console.warn(`Unknown MIME type for ${item.url}, defaulting to image/jpeg`);
                            }
                    }

                    let fileName = item.filename;
                    if (!fileName.includes('.')) {
                        fileName = `image-${index + 1}.${fileExtension}`;
                    } else if (!fileName.endsWith(`.${fileExtension}`)) {
                        fileName = fileName.replace(/\.[^/.]+$/, `.${fileExtension}`);
                    }

                    return new File([blob], fileName, { type: mimeType });
                }).filter(file => file !== null);

                if (files.length > 0) {
                    const dataTransfer = new DataTransfer();
                    files.forEach(file => dataTransfer.items.add(file));
                    $fileInput[0].files = dataTransfer.files;
                    $fileInput.trigger('change');
                } else {
                    console.error('No valid images processed');
                    $('.ai-btn-insert').prop("disabled",false);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error processing images:', error);
                $('.ai-btn-insert').prop("disabled",false);
            },
            complete: function() {
                $modal.modal('hide');
                $modal.on('hidden.bs.modal', function() {
                    $fileInput.focus();
                    $(this).off('hidden.bs.modal');
                });
                $('.ai-btn-insert').prop("disabled",false);
                $('.ai-btn-insert').html(`{{translate('Insert')}}`)
            },
        });
    });

    $(document).on('click', '.download-image', function(e) {
        e.preventDefault();

        const modalName = $(this).data('modal');
        const $modal = $(`#${modalName}`);

        const imageUrls = $modal.find('.image-check:checked').map(function() {
            return $(this).val();
        }).get();

        if (!imageUrls || imageUrls.length === 0) {
            console.log('Error: No image URLs found in #image_urls');
            return;
        }

        $.ajax({
            url: '{{ route("process.images") }}',
            method: 'GET',
            data: {
                urls: imageUrls
            },

            beforeSend: function() {
                $('.ai-btn-download').prop("disabled",true);
                $('.ai-btn-download').html(`{{translate('Download')}}`)
                $('.ai-btn-download').html(`{{translate('Download')}}<div class="spinner-border spinner-border-sm text-white" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>`)

            },
            success: function(response) {
                response.forEach(function(item, index) {
                    if (item.error) {
                        console.error('Error for image:', item.url, item.error);
                        return;
                    }

                    var binary = atob(item.content);
                    var array = new Uint8Array(binary.length);
                    for (var i = 0; i < binary.length; i++) {
                        array[i] = binary.charCodeAt(i);
                    }

                    var blob = new Blob([array], { type: item.content_type });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);

                    link.download = item.filename;

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    window.URL.revokeObjectURL(link.href);
                });
            },
            error: function(xhr, status, error) {
                console.error('Error downloading images:', error);
            },
            complete: function() {
                $('.ai-btn-download').prop("disabled",false);
                $('.ai-btn-download').html(`{{translate('Download')}}`)
            },

        });
    });

    $(document).on('input', '#image-search', function (e) {

        const modalName = $(this).data('modal');
        const $modal = $(`#${modalName}`);

        const searchTerm    = $(this).val().toLowerCase();
        const $imageContent = $modal.find('#image-content');

        $modal.find('.image-card-container').each(function () {
            const imageName = $(this).data('name').toLowerCase();
            if (imageName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });


        const visibleCards = $modal.find('.image-card-container:visible').length;

        $imageContent.find('.no-results-message').remove();

        if (visibleCards === 0) {
            $imageContent.append('<div class="no-results-message text-center text-muted mt-3">No results found</div>');
        }


    });


    $(document).on('click', '.insert-video', function (e) {
            e.preventDefault();

            if ($(this).hasClass('disabled')) return;
            $(this).addClass('disabled');

            const modalName = $(this).data('modal');
            const $modal    = $(`#${modalName}`);
            const $btn      = $(this);

            const videoUrls = $modal.find('.video-check:checked').map(function () {
                return $(this).val();
            }).get();

            if (!videoUrls || videoUrls.length === 0) {
                console.log('Error: No video URLs found');
                $(this).removeClass('disabled');
                return;
            }

            const $platformNote = $('.platform-note');
            const $socialPreviewBody = $('.social-preview-body');
            const $fileInput = $('#media-file');



            requestAnimationFrame(() => {
                $platformNote.addClass('d-none');
                $socialPreviewBody.removeClass('d-none');
            });

            $.ajax({
                url: '{{ route("process.videos") }}',
                method: 'POST',
                data: {
                    "_token": "{{ csrf_token()}}",
                    "urls" : videoUrls
                },
                beforeSend: function () {
                    $('.ai-btn-insert').prop("disabled",true);
                    $('.ai-btn-insert').html(`{{translate('Insert')}}`)
                    $('.ai-btn-insert').html(`{{translate('Insert')}}<div class="spinner-border spinner-border-sm text-white" role="status">
                                            <span class="visually-hidden"></span>
                                        </div>`)

                },
                success: function (response) {

                    const files = response.map(function (item, index) {
                        if (item.error) {
                            console.error('Error for video:', item.url, item.error);
                            return null;
                        }

                        var binary = atob(item.content);
                        var array = new Uint8Array(binary.length);
                        for (var i = 0; i < binary.length; i++) {
                            array[i] = binary.charCodeAt(i);
                        }

                        var blob = new Blob([array], { type: item.content_type });

                        let fileExtension = 'mp4';
                        let mimeType = item.content_type || 'video/mp4';

                        switch (item.content_type) {
                            case 'video/mp4':
                                fileExtension = 'mp4';
                                mimeType = 'video/mp4';
                                break;
                            case 'video/webm':
                                fileExtension = 'webm';
                                mimeType = 'video/webm';
                                break;
                            case 'video/ogg':
                                fileExtension = 'ogg';
                                mimeType = 'video/ogg';
                                break;
                            default:
                                if (!item.content_type) {
                                    console.warn(`Unknown MIME type for ${item.url}, defaulting to video/mp4`);
                                }
                        }

                        let fileName = item.filename;
                        if (!fileName.includes('.')) {
                            fileName = `video-${index + 1}.${fileExtension}`;
                        } else if (!fileName.endsWith(`.${fileExtension}`)) {
                            fileName = fileName.replace(/\.[^/.]+$/, `.${fileExtension}`);
                        }

                        return new File([blob], fileName, { type: mimeType });
                    }).filter(file => file !== null);

                    if (files.length > 0) {
                        const dataTransfer = new DataTransfer();
                        files.forEach(file => dataTransfer.items.add(file));
                        $fileInput[0].files = dataTransfer.files;
                        $fileInput.trigger('change');
                    } else {
                        console.error('No valid videos processed');
                        $('.ai-btn-insert').prop("disabled",false);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error processing videos:', error);
                    $('.ai-btn-insert').prop("disabled",false);
                },
                complete: function () {
                    $modal.modal('hide');
                    $modal.on('hidden.bs.modal', function () {
                        $fileInput.focus();
                        $(this).off('hidden.bs.modal');
                    });

                    $('.ai-btn-insert').prop("disabled",false);
                    $('.ai-btn-insert').html(`{{translate('Insert')}}`)

                },
            });
        });



    $(document).on('click', '.download-video', function (e) {
        e.preventDefault();

        const modalName = $(this).data('modal');
        const $modal = $(`#${modalName}`);
        const $btn   = $(this);

        const videoUrls = $modal.find('.video-check:checked').map(function () {
            return $(this).val();
        }).get();

        if (!videoUrls || videoUrls.length === 0) {
            console.log('Error: No video URLs found ');
            return;
        }

        $.ajax({
            url: '{{ route("process.videos") }}',
            method: 'POST',
            data: {
                "_token": "{{ csrf_token()}}",
                "urls" : videoUrls
            },

            beforeSend: function () {
                $('.ai-btn-download').prop("disabled",true);
                $('.ai-btn-download').html(`{{translate('Download')}}`)
                $('.ai-btn-download').html(`{{translate('Download')}}<div class="spinner-border spinner-border-sm text-white" role="status">
                                        <span class="visually-hidden"></span>
                                    </div>`)

            },
            success: function (response) {
                response.forEach(function (item, index) {
                    if (item.error) {
                        console.error('Error for video:', item.url, item.error);
                        return;
                    }

                    var binary = atob(item.content);
                    var array = new Uint8Array(binary.length);
                    for (var i = 0; i < binary.length; i++) {
                        array[i] = binary.charCodeAt(i);
                    }

                    var blob = new Blob([array], { type: item.content_type });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);

                    link.download = item.filename;

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    window.URL.revokeObjectURL(link.href);
                });
            },
            error: function (xhr, status, error) {
                console.error('Error downloading images:', error);
                $('.ai-btn-download').prop("disabled",false);
                $('.ai-btn-download').html(`{{translate('Download')}}`)

            },
            complete: function () {
                $('.ai-btn-download').prop("disabled",false);
                $('.ai-btn-download').html(`{{translate('Download')}}`)

            },

        });
    });


    function formatState(state) {
        if (!state.id) {
            return state.text;
        }
        var baseUrl = $(state.element).data('image');
        var $state = $( '<span class="image-option"><img src="' + baseUrl + '" class="img-flag" /> ' + $('<div>').text(state.text).html() + '</span>' );
        return $state;
    }

    $('.profile-select').select2({
        templateResult: formatState,
        templateSelection: formatState,
    });

    function execCommandWithPreventDefault(command) {
        return function(event) {
            event.preventDefault();
            document.execCommand(command, false, null);
        };
    }


    var start = null;
    var end = null;

    function cb(start, end) {
        if (start) {
            const formattedDate = start.format('YYYY-MM-DDTHH:mm');
            const humanReadableDate = start.format('MMMM D, YYYY h:mm A');
            var cleanContent = DOMPurify.sanitize(humanReadableDate);
            $('#schedule_date_input').val(formattedDate);
            $('.show-date').html(`
                    <span class="pe-3">${cleanContent}
                    <i class="bi bi-x ps-2 fs-6 text--danger pointer  clear-input "></i></span>`);

        } else {
            $('#schedule_date_input').val('');
            $('.show-date').html('');
        }
    }


        $('#schedule_date_picker').daterangepicker(
            {
                singleDatePicker: true,
                timePicker: true,
                timePicker24Hour: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-MM-DDTHH:mm'
                }
            },
            cb
        );

        $('#schedule_date_picker').on('apply.daterangepicker', function (ev, picker) {
            cb(picker.startDate, picker.endDate);
        });

        $(document).on('click', '.clear-input',function(e){
            e.preventDefault()
            cb(null, null);
        })
        cb(start, end);


    const selectTwo = document.querySelector(".select-two");
  $(document).ready(function () {
    if (selectTwo) {
      $(selectTwo).select2(
        {
            placeholder: "Select a state",
        }
      );
    }
  });

//   Gallery
function updateActionButtons() {
        const checkedCount = document.querySelectorAll('.gallery-checkbox:checked').length;
        const actionButtons = document.getElementById('actionButtons');

        if (checkedCount > 0) {
            actionButtons.classList.remove('d-none');
            actionButtons.classList.add('d-block');
        } else {
            actionButtons.classList.remove('d-block');
            actionButtons.classList.add('d-none');
        }
        }

        document.getElementById('checkAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.gallery-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateActionButtons();
        });

        document.querySelectorAll('.gallery-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.tagName.toLowerCase() !== 'input') {
            const checkbox = this.querySelector('.gallery-checkbox');
            checkbox.checked = !checkbox.checked;
            updateActionButtons();
            }
        });
        });

        document.querySelectorAll('.gallery-checkbox').forEach(checkbox => {
        checkbox.addEventListener('click', function(e) {
            e.stopPropagation();
            updateActionButtons();
        });
        });

})(jQuery);

</script>
@endpush


