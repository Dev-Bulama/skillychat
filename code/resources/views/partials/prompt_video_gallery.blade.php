<div class="col-xl-3 col-lg-4 col-sm-6 mb-4">
    <div class="card gallery-card">
        <div class="gallery-overlay">
            <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="form-check form-switch">
                    <input {{ !check_permission('update_content') ? 'disabled' : '' }} type="checkbox" class="status-update form-check-input"
                        data-column="status"
                        data-route="{{ route('admin.content.update.status') }}"
                        data-status="{{ $content->status == App\Enums\StatusEnum::true->status() ? App\Enums\StatusEnum::false->status() : App\Enums\StatusEnum::true->status() }}"
                        data-id="{{ $content->uid }}"
                        {{ $content->status == App\Enums\StatusEnum::true->status() ? 'checked' : '' }}
                        id="status-switch-{{ $content->id }}">
                    <label class="form-check-label" for="status-switch-{{ $content->id }}"></label>
                </div>


                <div class="d-flex justify-content-end gap-2">
                    @if(check_permission('update_content'))
                        <a href="javascript:void(0);" class="update btn btn-sm btn-warning" data-content="{{ json_encode($content) }}">
                            <i class="las la-pen"></i>
                        </a>
                    @endif

                    @if(check_permission('delete_content'))
                        <a href="javascript:void(0);" class="delete-item btn btn-sm btn-danger" data-href="{{ route('admin.content.destroy', $content->id) }}">
                            <i class="las la-trash-alt"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body d-flex flex-column justify-content-between">

            <div class="image-content-wrap">

                @if($content->file && $content->file->isNotEmpty())
                    <video class="mb-3 w-100" controls>
                        <source src="{{ imageURL(@$content->file->first(), 'content', true) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    
                @else
                    <img src="{{ asset('assets/images/default/placeholder.jpg') }}" alt="No Video" class="mb-3">
                @endif


            </div>

            <h6 class="card-title mb-0 text-start">{{ $content->name }}</h6>

        </div>
    </div>
</div>
