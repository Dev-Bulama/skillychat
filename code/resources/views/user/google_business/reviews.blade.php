@extends('layouts.master')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-10">

            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 fw-semibold">
                        <i class="bi bi-star me-2" style="color:#fbbc04;"></i>
                        {{ translate('Customer Reviews') }}
                    </h4>
                    <p class="text-muted mb-0 fs-14">{{ $location->display_name }}</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <form method="post" action="{{ route('user.google-business.sync-reviews', $location->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="i-btn btn--sm btn--outline">
                            <i class="bi bi-arrow-clockwise me-1"></i>{{ translate('Sync Reviews') }}
                        </button>
                    </form>
                    <a href="{{ route('user.google-business.index') }}" class="i-btn btn--sm btn--outline">
                        <i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}
                    </a>
                </div>
            </div>

            @if(session('status'))
                <div class="alert alert-{{ session('type','success') === 'error' ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Filter Bar --}}
            <div class="i-card-md mb-4">
                <div class="card-body py-2">
                    <div class="d-flex gap-2 flex-wrap">
                        @php
                            $filters = ['all' => translate('All'), 'needs_reply' => translate('Needs Reply'), 'low' => translate('1–2 Stars'), 'high' => translate('3–5 Stars')];
                        @endphp
                        @foreach($filters as $key => $label)
                            <a href="{{ request()->fullUrlWithQuery(['filter' => $key]) }}"
                               class="i-btn btn--sm {{ $filter === $key ? 'btn--primary' : 'btn--outline' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Review Cards --}}
            @forelse($reviews as $review)
            <div class="i-card-md mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        {{-- Avatar --}}
                        <div class="flex-shrink-0">
                            @if($review->reviewer_photo)
                                <img src="{{ $review->reviewer_photo }}" alt="{{ $review->reviewer_name }}"
                                     class="rounded-circle" width="44" height="44"
                                     style="object-fit:cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                                     style="width:44px;height:44px;font-size:18px;">
                                    {{ strtoupper(substr($review->reviewer_name ?? 'G', 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                <div>
                                    <strong>{{ $review->reviewer_name ?? translate('Anonymous') }}</strong>
                                    <span class="ms-2">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="bi bi-star{{ $s <= $review->star_rating ? '-fill' : '' }}"
                                               style="color:#fbbc04;font-size:13px;"></i>
                                        @endfor
                                    </span>
                                </div>
                                <div>
                                    @if($review->status === 'replied')
                                        <span class="badge bg-success">{{ translate('Replied') }}</span>
                                    @elseif($review->status === 'ignored')
                                        <span class="badge bg-secondary">{{ translate('Ignored') }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">{{ translate('Pending') }}</span>
                                    @endif
                                    @if($review->create_time)
                                        <small class="text-muted ms-2">{{ \Carbon\Carbon::parse($review->create_time)->format('M j, Y') }}</small>
                                    @endif
                                </div>
                            </div>
                            @if($review->comment)
                                <p class="mb-2 text-muted">{{ $review->comment }}</p>
                            @else
                                <p class="mb-2 text-muted fst-italic fs-13">{{ translate('(No comment)') }}</p>
                            @endif

                            {{-- Existing Reply --}}
                            @if($review->reply)
                            <div class="p-3 rounded-3 mb-3" style="background:#f0f9ff;border-left:3px solid #3b82f6;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong class="fs-13 text-primary"><i class="bi bi-reply me-1"></i>{{ translate('Your Reply') }}</strong>
                                    <form method="post" action="{{ route('user.google-business.review.reply.delete', [$location->id, urlencode($review->review_name)]) }}"
                                          onsubmit="return confirm('{{ translate('Delete this reply?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0 fs-12">
                                            <i class="bi bi-trash me-1"></i>{{ translate('Delete Reply') }}
                                        </button>
                                    </form>
                                </div>
                                <p class="mb-0 fs-14 text-muted">{{ $review->reply }}</p>
                            </div>
                            @endif

                            {{-- Reply Box --}}
                            <div class="reply-box" id="reply-box-{{ $review->id }}" style="{{ $review->reply ? 'display:none;' : '' }}">
                                <form method="post"
                                      action="{{ route('user.google-business.review.reply', [$location->id, urlencode($review->review_name)]) }}">
                                    @csrf
                                    <div class="mb-2">
                                        <textarea name="reply" class="form-control form-control-sm" rows="3"
                                            placeholder="{{ translate('Write your reply...') }}"
                                            id="reply-text-{{ $review->id }}">{{ old('reply') }}</textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="i-btn btn--sm btn--primary">
                                            <i class="bi bi-send me-1"></i>{{ translate('Post Reply') }}
                                        </button>
                                        <button type="button"
                                            class="i-btn btn--sm btn--outline ai-draft-btn"
                                            data-review-id="{{ $review->id }}"
                                            data-comment="{{ e($review->comment) }}"
                                            data-stars="{{ $review->star_rating }}">
                                            <i class="bi bi-magic me-1"></i>{{ translate('AI Draft') }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            @if($review->reply)
                            <button type="button" class="btn btn-link p-0 fs-13 text-primary mt-1"
                                    onclick="document.getElementById('reply-box-{{ $review->id }}').style.display='block'; this.style.display='none';">
                                <i class="bi bi-pencil me-1"></i>{{ translate('Edit Reply') }}
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="i-card-md">
                <div class="card-body text-center py-5">
                    <i class="bi bi-star fs-1 text-muted mb-3 d-block"></i>
                    <h6>{{ translate('No reviews found') }}</h6>
                    <p class="text-muted fs-14">{{ translate('Reviews will appear here once customers leave feedback on Google.') }}</p>
                </div>
            </div>
            @endforelse

            {{-- Pagination --}}
            @if($reviews->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $reviews->appends(request()->query())->links() }}
            </div>
            @endif

        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
document.querySelectorAll('.ai-draft-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var reviewId = this.dataset.reviewId;
        var comment  = this.dataset.comment;
        var stars    = this.dataset.stars;
        var textarea = document.getElementById('reply-text-' + reviewId);
        var self     = this;

        self.disabled = true;
        self.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>{{ translate("Generating...") }}';

        fetch('/user/ai-content/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify({
                prompt: 'Write a professional, friendly reply to this ' + stars + '-star Google review: "' + comment + '". Keep it under 200 words.',
                type: 'text'
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data && data.content) {
                textarea.value = data.content;
            }
        })
        .catch(function() {
            textarea.value = 'Thank you for your feedback! We appreciate you taking the time to share your experience with us.';
        })
        .finally(function() {
            self.disabled = false;
            self.innerHTML = '<i class="bi bi-magic me-1"></i>{{ translate("AI Draft") }}';
        });
    });
});
</script>
@endpush

@endsection
