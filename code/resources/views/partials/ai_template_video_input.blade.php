@php
$iconClass = "las la-question-circle text--danger";
if(@$user) $iconClass = "bi bi-info-circle text--danger";
@endphp

<input type="hidden" value="{{App\Enums\StatusEnum::false->status()}}" name="custom_prompt" id="custom_prompt">
<div class="video-template-input-section">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="lh-1">{{translate('Generate Video')}}</h4>
        <a href="javascript:void(0)" class="i-btn btn--sm danger select-template" data-templateType="video">
            <i class="bi bi-arrow-repeat"></i>
        </a>
    </div>

    @if (@$template)
    <ul class="ai-post-meta-list">
        @php
        $category = $template->category;
        $subCategory = $template->subCategory;
        if( $subCategory){
        $category = $template->subCategory;
        }
        @endphp
        <li><span> {{translate('Category')}} :</span> <i class="{{$category->icon}}"></i> {{$category->title}} </li>
        <li><span> {{translate('Template')}} :</span> <i class="{{$template->icon}}"></i> {{$template->name}} </li>
    </ul>
    @endif

    <div class="ai-from-wrapper template-prompt">
        <input type="hidden" value="{{App\Enums\StatusEnum::true->status()}}" name="custom_prompt" id="custom_prompt">
        @if(@$template && @$template->prompt_fields)
        @foreach($template->prompt_fields as $key => $input)

        <div class="mb-3">
            <label for="{{$key }}">
                {{@$input->field_label}}
                @if(@$input->validation == 'required') <small class="text-danger">*</small> @endif
                @if(@$input->instraction)
                <span class="custom--tooltip">
                    <i class="bi bi-info-circle-fill text--info"></i>
                    <span class="tooltip-text">
                        {{@$input->instraction}}
                    </span>
                </span>
                @endif
            </label>

            @if ($input->type == "text")
            <input data-name="{{ '{'.@$input->field_name.'}'}}" placeholder="{{@$input->field_label}}"
                name="custom[{{@$input->field_name}}]" {{@$input->validation == 'required' ? 'required' : ''}}
            type="text" id="{{ $key }}" class="prompt-input" value="{{old('custom'.@$input->field_name)}}">
            @else
            <textarea data-name=" '{' . {{ @$input->field_name }} . '}' " placeholder="{{@$input->field_label}}"
                name="custom[{{@$input->field_name}}]"
                {{@$input->validation == 'required' ? 'required' : ''}} type="text" id="{{ $key }}"  class="prompt-input">{{old('custom'.@$input->field_name)}}</textarea>
            @endif
        </div>

        @endforeach

        @endif

        <div class="mb-3">
            <label for="promptPreview">
                {{ translate('Prompt')}} <span class="text--danger">*</span>
            </label>
            <textarea required @if (@$template) data-prompt_input="{{$template->custom_prompt}}" readonly @else
                placeholder="{{translate('Enter your prompt')}}" @endif name="custom_prompt_input" id="promptPreview"
                cols="5" rows="5">@if(@$template){{$template->custom_prompt}} @endif</textarea>
        </div>

        <div class="content-gen-right">
            <h6 class="mb-2">
                {{translate('Advanced Option')}}
            </h6>

            <div class="advnced-option-card">
                <div class="row g-3">

                    <div class="col-xxl-6 col-md-6">

                        <label for="video-resolution" class="form-label">
                            {{translate('Output Aspect Ratio')}} <small class="text-danger">*</small>
                        </label>

                        <select name="video_aspect_ratio" class="form-select" id="video-resolution">


                            <option value="">
                                {{translate("Select Ratio")}}
                            </option>

                            @foreach (Arr::get(config('settings'),'ai_video_aspect_ratio',[]) as $k => $v )
                            <option {{site_settings('video_aspect_ratio')==$k ? 'selected' : '' }} value="{{$k}}">{{ $v }}
                            </option>
                            @endforeach

                        </select>

                    </div>

                    <div class="col-xxl-6 col-md-6">
                        <label for="video_duration" class="form-label">
                            {{translate("Video Duration")}} <i data-bs-toggle="tooltip" data-bs-placement="top"
                                data-bs-title="{{translate('Not all module support video duratoin , it will be applied where if supported ')}}"
                                class="ms-1 pointer {{$iconClass}}"></i>
                            @if(request()->routeIs('user.*'))
                            <span class="text--danger">*</span>
                            @endif
                        </label>
                        <input @if(request()->routeIs('user.*')) required @endif placeholder="{{translate('Enter
                        number')}}" type="number" min="1"
                        id="video_duration" name="video_duration" value='5' >
                    </div>


                </div>
            </div>
        </div>

        <div class="d-flex gap-3  w-100 mt-4">


            <button type="submit" class="i-btn btn--primary {{ @$user || $is_user_request  ? " btn--lg capsuled"
                : "btn--md" }} postSubmitButton ai-btn me-2">
                {{translate('Generate')}}
                <i class="bi bi-send ms-2 generate-icon-btn"></i>
            </button>
        </div>
    </div>
</div>
