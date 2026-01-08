
<div  class="content-section">

    <style>
        .ai-content-div .card {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }
        .ai-content-div .card-img-top {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0.25rem;
        }
    </style>

    @php
        $generateRoute = route('admin.ai.template.content.generate.image');
        $iconClass  = "las la-question-circle text--danger";

        if(request()->routeIs('user.*')){
            $generateRoute  =  route('user.ai.content.image.generate');
            $iconClass      = "bi bi-info-circle text--danger";
        }
    @endphp

    <div class="@if(!@$modal) @endif @if(@$modal) border-0 @endif" id="ai-image-form">

        @if(!@$modal)
            <div class="{{request()->routeIs('user.*') ? 'card-header' :'card--header' }} mb-4">
                <h4 class="card-title">
                    {{translate("Image content Categories")}}
                </h4>
            </div>
        @endif

        <form data-route="{{$generateRoute}}" class="ai-content-image-form {{request()->routeIs('user.*') ? 'p-4 pt-0' :'' }}" >

            @csrf

            <input type="hidden" name="id" id="imageTemplateId">

            <div class="row g-4 template-selection-section">
                <div class="col-xl-4 col-lg-5">
                    <div class="template-sidebar" data-simplebar>

                        @include('admin.partials.card_loader',['customer_class' => "template-category-loader"])

                        <div class="template-categories image-category-section">
                            @include("partials.template.list",['categories' => $categories->where('ai_module_type' , \App\Enums\AiModuleType::IMAGE->value)])
                        </div>


                    </div>
                </div>

                <div class="col-xl-8 col-lg-7 position-relative">

                     @include('admin.partials.card_loader' ,['customer_class' => 'input-section-loader'])

                    <div class="image-template-input-section">

                        <h4 class="lh-1 mb-3">
                            {{translate('Generate Image')}}
                        </h4>

                        <div class="ai-from-wrapper template-prompt">

                            <div id="contentPreviewSection" class="d-none">
                                <div class="mb-3">
                                    <label for="postPreview">
                                        {{translate('Post content')}}
                                    </label>
                                    <textarea readonly name="post_content" id="postPreview" cols="5" rows="4"></textarea>
                                </div>

                                <div class="d-flex gap-3 justify-content-start w-100 mt-4 mb-3">
                                    <a class="i-btn btn--primary {{(request()->routeIs('user.*')) ? 'btn--lg capsuled' : 'btn--md'}} gap-2 prompt-from-content ai-btn-prompt"
                                        data-route="{{ request()->routeIs('admin.*') ? route('admin.ai.template.generate.prompt') : route('user.ai.content.generate.prompt') }}"
                                        data-type="image">

                                        {{translate('Generate prompt from content')}}
                                        <i class="bi bi-robot generate-icon-btn"></i>
                                    </a>
                                </div>
                            </div>

                            <input type="hidden"
                                    value="{{App\Enums\StatusEnum::true->status()}}"
                                    name="custom_prompt" id="custom_prompt">

                            <div class="mb-3">

                                <label for="promptPreview">
                                    {{ translate('Prompt')}}  <span class="text--danger" >*</span>
                                </label>

                                <textarea required  @if (@$template) data-prompt_input="{{$template->custom_prompt}}" readonly @else placeholder="{{translate('Enter your prompt')}}" @endif name="custom_prompt_input"   id="promptPreview" cols="5" rows="5">@if(@$template){{$template->custom_prompt}} @endif</textarea>

                            </div>

                            <div class="content-gen-right">
                                <h6 class="mb-2">
                                    {{translate('Advanced Option')}}
                                </h6>

                                <div class="advnced-option-card">
                                    <div class="row g-3">

                                        <div class="col-xxl-6 col-md-6">

                                            <label for="image-resolution" class="form-label">
                                                {{translate('Output Image Resolution')}} <small class="text-danger">*</small>
                                            </label>

                                            <select name="image_resolution" class="form-select" id="image-resolution">


                                                <option value="">
                                                    {{translate("Select Resolution")}}
                                                </option>

                                                @foreach (Arr::get(config('settings'),'ai_image_resolution',[]) as $k => $v )
                                                    <option {{site_settings('image_resolution') == $k ? 'selected' : ''}} value="{{$k}}" >{{ $v }}</option>
                                                @endforeach

                                            </select>

                                        </div>

                                        <div class="col-xxl-6 col-md-6">
                                            <label for="max_result" class="form-label">
                                                {{translate("Max Images")}} <i  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('Maximum image number for each result')}}"  class="ms-1 pointer {{$iconClass}}"></i>
                                                @if(request()->routeIs('user.*'))
                                                    <span class="text--danger">*</span>
                                                @endif
                                            </label>
                                            <input @if(request()->routeIs('user.*')) required @endif   placeholder="{{translate('Enter number')}}" type="number" min="1"
                                            id="max_result" name="max_result"  value='1' >
                                        </div>


                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3 justify-content-start    w-100 mt-4">
                                <button type="submit" class="i-btn btn--primary {{(request()->routeIs('user.*')) ? "btn--lg capsuled" : "btn--md"}}   postSubmitButton ai-btn gap-2">
                                    {{translate('Generate')}}
                                    <i class="bi bi-send  generate-icon-btn"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    @if(!@$modal)
        <div class="d-none ai-content-div">
            <div class="{{request()->routeIs('user.*') ? 'card-header' :'card--header' }}">
                <h4 class="card-title">
                    {{translate("Image Content")}}
                </h4>
            </div>

            <div class="row {{request()->routeIs('user.*') ? 'p-4' :'' }}">
                <div class="col-lg-12 d-flex justify-content-end">
                    @if(request()->routeIs('admin.*'))
                        <div class="action">
                            <a href="{{route('admin.content.image.list')}}"    class="i-btn btn--sm success">
                                <i class="las la-arrow-left me-1"></i>  {{translate('Back')}}
                            </a>
                        </div>
                    @else
                        <a href="{{route('user.ai.content.list')}}" class="i-btn primary btn--sm capsuled">
                            <i class="bi bi-arrow-left"></i>
                            {{translate('Back')}}
                        </a>
                    @endif
                </div>

                <form action="{{$content_route}}" class="content-form" enctype="multipart/form-data" method="post">
                    @csrf

                    <div class="col-lg-12 ai-content-div">
                        <div class="form-inner">
                            <label>{{ translate('Generated Image') }} <small class="text-danger">*</small></label>
                            <div id="image-content" class="row">
                                <!-- Images -->
                            </div>

                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class=" {{request()->routeIs('user.*') ? 'i-btn btn--lg btn--primary capsuled' : 'i-btn btn--md btn--primary'}}  " data-anim="ripple">
                            {{translate("Save")}}
                            @if(request()->routeIs('user.*'))
                                <span><i class="bi bi-arrow-up-right"></i></span>
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="d-none ai-content-div">
            <div class="content-form">
                <div class="form-inner mb-0">
                    <label>{{ translate('Content') }} <small class="text-danger">*</small></label>
                    <div id="image-content" class="row">
                        <!-- Images -->
                    </div>

                    <input type="hidden" name="image_urls" id="image_urls" value="">
                </div>

                <div class="text-end mt-4">
                    <div class="d-flex gap-2 align-items-center justify-content-end flex-wrap">
                        <button data-anim="ripple" data-modal="aiImageModal"
                        class="{{request()->routeIs('user.*') ? 'i-btn primary btn--sm gap-2 capsuled' : 'i-btn btn--primary-transparent btn--sm  gap-2'}}  insert-image ai-btn-insert">
                            <i class="bi bi-box-arrow-down"></i>
                            {{translate("Insert")}}
                       </button>

                        <button data-anim="ripple" data-modal="aiImageModal"
                            class="{{request()->routeIs('user.*') ? 'i-btn btn--info-transparent btn--sm gap-2 capsuled' : 'i-btn btn--info-transparent btn--sm  gap-2'}} download-image ai-btn-download">
                            <i class="bi bi-download"></i>
                            {{translate("Download")}}
                        </button>
                    </div>

                    <div class="mt-4 d-flex align-items-center justify-content-end">
                        <button
                            class="bg-transparent p-0 text-danger fw-normal resubmit-ai-image-form d-flex align-items-center lh-1">
                            {{translate("Not satisfy? Retry")}}

                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
