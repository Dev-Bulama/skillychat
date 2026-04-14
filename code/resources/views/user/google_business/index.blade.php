@extends('layouts.master')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-10">

            {{-- Page Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1 fw-semibold">
                        <i class="bi bi-building me-2" style="color:#4285f4;"></i>
                        {{ translate('Google Business Profile') }}
                    </h4>
                    <p class="text-muted mb-0 fs-14">{{ translate('Manage your Google Business locations, reviews, posts and insights.') }}</p>
                </div>
            </div>

            @if(session('status'))
                <div class="alert alert-{{ session('type','success') === 'error' ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(! $account)
            {{-- ── Not Connected State ──────────────────────────────────────── --}}
            <div class="i-card-md">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:80px;height:80px;">
                            <i class="bi bi-building fs-1" style="color:#4285f4;"></i>
                        </div>
                    </div>
                    <h5 class="fw-semibold mb-2">{{ translate('Connect Your Google Business Profile') }}</h5>
                    <p class="text-muted mb-4 mx-auto" style="max-width:480px;">
                        {{ translate('Connect your Google account to manage your business locations, respond to customer reviews, create posts, and view performance insights — all from one place.') }}
                    </p>
                    <div class="row justify-content-center g-3 mb-4">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border text-start h-100">
                                <i class="bi bi-star-fill text-warning mb-2 d-block fs-5"></i>
                                <strong class="d-block fs-14">{{ translate('Manage Reviews') }}</strong>
                                <small class="text-muted">{{ translate('Read and reply to customer reviews from your dashboard.') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border text-start h-100">
                                <i class="bi bi-megaphone-fill text-primary mb-2 d-block fs-5"></i>
                                <strong class="d-block fs-14">{{ translate('Create Posts') }}</strong>
                                <small class="text-muted">{{ translate('Publish updates, offers and events directly to Google.') }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 border text-start h-100">
                                <i class="bi bi-graph-up-arrow text-success mb-2 d-block fs-5"></i>
                                <strong class="d-block fs-14">{{ translate('View Insights') }}</strong>
                                <small class="text-muted">{{ translate('Track searches, map views, calls and website clicks.') }}</small>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('user.google-business.connect') }}" class="i-btn btn--md btn--primary">
                        <i class="bi bi-google me-2"></i>{{ translate('Connect with Google') }}
                    </a>
                </div>
            </div>

            @else
            {{-- ── Connected State ──────────────────────────────────────────── --}}

            {{-- Account Info Bar --}}
            <div class="i-card-md mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width:48px;height:48px;">
                                <i class="bi bi-google fs-4" style="color:#4285f4;"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $account->google_email ?? translate('Google Account') }}</div>
                                <div>
                                    @if($account->status === 'connected')
                                        <span class="badge bg-success">{{ translate('Connected') }}</span>
                                    @elseif($account->status === 'expired')
                                        <span class="badge bg-warning text-dark">{{ translate('Token Expired') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ translate('Disconnected') }}</span>
                                    @endif
                                    <small class="text-muted ms-2">{{ translate('Connected') }} {{ $account->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <form method="post" action="{{ route('user.google-business.sync-locations') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="i-btn btn--sm btn--outline">
                                    <i class="bi bi-arrow-clockwise me-1"></i>{{ translate('Sync Locations') }}
                                </button>
                            </form>
                            <a href="{{ route('user.google-business.diagnose') }}" target="_blank" class="i-btn btn--sm btn--outline" title="{{ translate('Show raw API responses for debugging') }}">
                                <i class="bi bi-bug me-1"></i>{{ translate('Diagnose API') }}
                            </a>
                            <form method="post" action="{{ route('user.google-business.disconnect') }}"
                                  class="d-inline"
                                  onsubmit="return confirm('{{ translate('Disconnect your Google account? This will remove all synced location data.') }}')">
                                @csrf
                                <button type="submit" class="i-btn btn--sm btn--danger">
                                    <i class="bi bi-x-circle me-1"></i>{{ translate('Disconnect') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Locations --}}
            @if($locations->isEmpty())
                <div class="i-card-md">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-geo-alt fs-1 text-muted mb-3 d-block"></i>
                        <h6 class="fw-semibold mb-1">{{ translate('No Locations Found') }}</h6>
                        <p class="text-muted fs-14">{{ translate('No business locations are linked to this Google account yet.') }}</p>
                        <form method="post" action="{{ route('user.google-business.sync-locations') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="i-btn btn--sm btn--primary mt-2">
                                <i class="bi bi-arrow-clockwise me-1"></i>{{ translate('Sync Locations Now') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="row g-4">
                    @foreach($locations as $location)
                    <div class="col-xl-6">
                        <div class="i-card-md h-100">
                            <div class="card--header">
                                <div class="d-flex align-items-start gap-2">
                                    <div>
                                        <h6 class="fw-semibold mb-1">
                                            {{ $location->display_name }}
                                            @if($location->is_verified)
                                                <i class="bi bi-patch-check-fill text-primary ms-1" title="{{ translate('Verified') }}"></i>
                                            @endif
                                        </h6>
                                        @if($location->address_string)
                                            <p class="text-muted fs-13 mb-0">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $location->address_string }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3">
                                    @if($location->phone)
                                    <div class="col-12">
                                        <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $location->phone }}</small>
                                    </div>
                                    @endif
                                    @if($location->website)
                                    <div class="col-12">
                                        <small class="text-muted"><i class="bi bi-globe me-1"></i>
                                            <a href="{{ $location->website }}" target="_blank" class="text-muted">{{ $location->website }}</a>
                                        </small>
                                    </div>
                                    @endif
                                    @if($location->last_sync_at)
                                    <div class="col-12">
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ translate('Last synced') }}: {{ $location->last_sync_at->diffForHumans() }}</small>
                                    </div>
                                    @endif
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('user.google-business.location', $location->id) }}" class="i-btn btn--sm btn--primary">
                                        <i class="bi bi-pencil me-1"></i>{{ translate('Edit Info') }}
                                    </a>
                                    <a href="{{ route('user.google-business.reviews', $location->id) }}" class="i-btn btn--sm btn--outline">
                                        <i class="bi bi-star me-1"></i>{{ translate('Reviews') }}
                                        @php $pending = $location->reviews()->where('status','pending')->count(); @endphp
                                        @if($pending > 0)
                                            <span class="badge bg-danger ms-1">{{ $pending }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('user.google-business.posts', $location->id) }}" class="i-btn btn--sm btn--outline">
                                        <i class="bi bi-megaphone me-1"></i>{{ translate('Posts') }}
                                    </a>
                                    <a href="{{ route('user.google-business.insights', $location->id) }}" class="i-btn btn--sm btn--outline">
                                        <i class="bi bi-graph-up me-1"></i>{{ translate('Insights') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif

            @endif
        </div>
    </div>
</div>

@endsection
