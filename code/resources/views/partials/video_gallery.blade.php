<div class="content-section">

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
        $iconClass = "las la-question-circle text--danger";

        if (request()->routeIs('user.*')) {
            $generateRoute = route('user.ai.content.image.generate');
            $iconClass = "bi bi-info-circle text--danger";
        }
    @endphp

    <div class="ai-content-div">
        <div class="content-form">
            <div class="form-inner mb-0">
                <label>{{ translate('Video Content') }} <small class="text-danger">*</small></label>

                <!-- Search Input -->
                <div class="mb-3">
                    <input type="text" id="image-search" data-modal="aiVideoGalleryModal" class="form-control"
                        placeholder="{{ translate('Search by video name...') }}">
                </div>

                <div id="image-content" class="row">
                    @forelse ($video_gallery_contents as $index => $content)
                        <div class="col-lg-3 col-md-4 mb-4 image-card-container" data-name="{{ $content->name }}">
                            <div class="card image-card position-relative">
                                @if ($content->file && $content->file->isNotEmpty())
                                    <video class="card-img-top" controls poster="{{ imageURL($content->file->first(), 'content', true, 'thumbnail') }}">
                                        <source src="{{ imageURL($content->file->first(), 'content', true) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="form-check position-absolute top-0 end-0 m-2">
                                        <input class="form-check-input video-check" name="video_urls[]" type="checkbox"
                                            value="{{ imageURL($content->file->first(), 'content', true) }}"
                                            id="check-{{ $index }}">
                                    </div>
                                    <div class="card-footer text-center">
                                        <small class="text-muted">{{ $content->name }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty

                        <div>

                            @include('admin.partials.not_found', ['custom_message' => "No Videos found!!"])

                        </div>
                    @endforelse

                </div>

            </div>

            <div class="text-end mt-4">
                <div class="d-flex gap-2 align-items-center justify-content-end flex-wrap">
                    <button data-anim="ripple" data-modal="aiVideoGalleryModal"
                        class="{{request()->routeIs('user.*') ? 'i-btn primary btn--sm gap-2 capsuled' : 'i-btn btn--primary-transparent btn--sm  gap-2'}} ai-btn-insert insert-video">
                        <i class="bi bi-box-arrow-down"></i>
                        {{translate("Insert")}}
                    </button>

                    <button data-anim="ripple" data-modal="aiVideoGalleryModal"
                        class="{{request()->routeIs('user.*') ? 'i-btn btn--info-transparent btn--sm gap-2 capsuled' : 'i-btn btn--info-transparent btn--sm  gap-2'}} ai-btn-download download-video">
                        <i class="bi bi-download"></i>
                        {{translate("Download")}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
