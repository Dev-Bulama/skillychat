@extends('layouts.master')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-10">

            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div>
                    <h4 class="mb-1 fw-semibold">
                        <i class="bi bi-megaphone me-2" style="color:#4285f4;"></i>
                        {{ translate('Google Posts') }}
                    </h4>
                    <p class="text-muted mb-0 fs-14">{{ $location->display_name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <form method="post" action="{{ route('user.google-business.sync-posts', $location->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="i-btn btn--sm btn--outline">
                            <i class="bi bi-arrow-clockwise me-1"></i>{{ translate('Sync Posts') }}
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

            <div class="row g-4">
                {{-- Create Post Form --}}
                <div class="col-xl-4">
                    <div class="i-card-md position-sticky" style="top:80px;">
                        <div class="card--header">
                            <h5 class="mb-0 fw-semibold"><i class="bi bi-plus-circle me-2"></i>{{ translate('Create New Post') }}</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('user.google-business.post.create', $location->id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ translate('Post Content') }} <span class="text-danger">*</span></label>
                                    <textarea name="summary" class="form-control @error('summary') is-invalid @enderror"
                                        rows="5"
                                        maxlength="1500"
                                        placeholder="{{ translate('Share an update, offer, or event...') }}"
                                        required>{{ old('summary') }}</textarea>
                                    <small class="text-muted fs-12">{{ translate('Max 1,500 characters') }}</small>
                                    @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">{{ translate('Call-to-Action') }}</label>
                                    <select name="call_to_action_type" class="form-select" id="ctaType">
                                        <option value="">{{ translate('None') }}</option>
                                        <option value="LEARN_MORE" {{ old('call_to_action_type') === 'LEARN_MORE' ? 'selected' : '' }}>{{ translate('Learn More') }}</option>
                                        <option value="CALL" {{ old('call_to_action_type') === 'CALL' ? 'selected' : '' }}>{{ translate('Call') }}</option>
                                        <option value="BOOK" {{ old('call_to_action_type') === 'BOOK' ? 'selected' : '' }}>{{ translate('Book') }}</option>
                                        <option value="ORDER" {{ old('call_to_action_type') === 'ORDER' ? 'selected' : '' }}>{{ translate('Order Online') }}</option>
                                        <option value="SIGN_UP" {{ old('call_to_action_type') === 'SIGN_UP' ? 'selected' : '' }}>{{ translate('Sign Up') }}</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="ctaUrlGroup" style="{{ old('call_to_action_type') ? '' : 'display:none;' }}">
                                    <label class="form-label fw-semibold">{{ translate('Button URL') }}</label>
                                    <input type="url" name="call_to_action_url" class="form-control @error('call_to_action_url') is-invalid @enderror"
                                        value="{{ old('call_to_action_url') }}"
                                        placeholder="https://example.com">
                                    @error('call_to_action_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <button type="submit" class="i-btn btn--md btn--primary w-100">
                                    <i class="bi bi-send me-1"></i>{{ translate('Publish Post') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Post List --}}
                <div class="col-xl-8">
                    @forelse($posts as $post)
                    <div class="i-card-md mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        @if($post->state === 'LIVE')
                                            <span class="badge bg-success">{{ translate('Live') }}</span>
                                        @elseif($post->state === 'REJECTED')
                                            <span class="badge bg-danger">{{ translate('Rejected') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $post->state }}</span>
                                        @endif
                                        @if($post->call_to_action_type)
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                {{ $post->call_to_action_type }}
                                            </span>
                                        @endif
                                        @if($post->create_time)
                                            <small class="text-muted ms-auto">{{ \Carbon\Carbon::parse($post->create_time)->format('M j, Y') }}</small>
                                        @endif
                                    </div>
                                    <p class="mb-1">{{ $post->summary }}</p>
                                    @if($post->call_to_action_url)
                                        <small class="text-muted"><i class="bi bi-link-45deg me-1"></i>
                                            <a href="{{ $post->call_to_action_url }}" target="_blank" class="text-muted">{{ $post->call_to_action_url }}</a>
                                        </small>
                                    @endif
                                </div>
                                <div class="flex-shrink-0">
                                    <form method="post"
                                          action="{{ route('user.google-business.post.delete', [$location->id, $post->id]) }}"
                                          onsubmit="return confirm('{{ translate('Delete this post?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="i-btn btn--sm btn--danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="i-card-md">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-megaphone fs-1 text-muted mb-3 d-block"></i>
                            <h6>{{ translate('No posts yet') }}</h6>
                            <p class="text-muted fs-14">{{ translate('Use the form to create your first Google Business post.') }}</p>
                        </div>
                    </div>
                    @endforelse

                    @if($posts->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $posts->links() }}
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}">
document.getElementById('ctaType').addEventListener('change', function() {
    document.getElementById('ctaUrlGroup').style.display = this.value ? 'block' : 'none';
});
</script>
@endpush

@endsection
