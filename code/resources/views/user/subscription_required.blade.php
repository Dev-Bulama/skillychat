@extends('layouts.master')
@section('content')

@php
    $user         = auth_user('web')->load('runningSubscription');
    $subscription = $user->runningSubscription;
    $isExpired    = $subscription && $subscription->expired_at && now()->gt(\Carbon\Carbon::parse($subscription->expired_at)->endOfDay());
@endphp

<div class="row g-4 justify-content-center">
    <div class="col-xl-7 col-lg-9">

        {{-- Status banner --}}
        <div class="i-card text-center py-5 px-4" style="border-top: 4px solid #7c2d3e;">
            <div class="mb-4">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                    style="width:80px;height:80px;background:#fff0f2;">
                    <i class="bi bi-lock fs-36" style="color:#7c2d3e;"></i>
                </span>
                <h3 class="fw-bold mb-2">
                    @if($isExpired)
                        {{ translate('Your Subscription Has Expired') }}
                    @else
                        {{ translate('Active Subscription Required') }}
                    @endif
                </h3>
                <p class="text-muted fs-15 mb-0" style="max-width:480px;margin:auto;">
                    @if($isExpired)
                        {{ translate('Your plan expired on') }}
                        <strong>{{ \Carbon\Carbon::parse($subscription->expired_at)->format('d M Y') }}</strong>.
                        {{ translate('Renew your subscription to restore access to Chatbot, Bulk SMS, and Bulk Email features.') }}
                    @else
                        {{ translate('This feature requires an active subscription. Choose a plan to unlock Chatbot, Bulk SMS, Bulk Email, and more.') }}
                    @endif
                </p>
            </div>

            @if(session('warning'))
            <div class="alert alert-warning fs-14 mb-4">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
            </div>
            @endif

            <div class="d-flex flex-wrap gap-3 justify-content-center mb-5">
                <a href="{{ route('user.plan') }}"
                    class="i-btn btn--lg capsuled"
                    style="background:#7c2d3e;color:#fff;border:none;min-width:180px;">
                    <i class="bi bi-lightning-charge me-2"></i>
                    {{ $isExpired ? translate('Renew Plan') : translate('View Plans') }}
                </a>
                <a href="{{ route('user.home') }}" class="i-btn btn--lg btn--outline capsuled">
                    <i class="bi bi-house me-2"></i>{{ translate('Back to Dashboard') }}
                </a>
            </div>

            {{-- What you unlock --}}
            <div class="border-top pt-4">
                <p class="fw-semibold text-muted fs-13 text-uppercase mb-3 letter-spacing-1">
                    {{ translate('Features Included With Any Plan') }}
                </p>
                <div class="row g-3 text-start">
                    @php
                    $features = [
                        ['icon' => 'bi-robot',          'color' => '#6d28d9', 'bg' => '#f3f0ff', 'label' => translate('AI Chatbot'),       'desc' => translate('Build and deploy chatbots for your website that handle customer queries 24/7.')],
                        ['icon' => 'bi-phone',           'color' => '#16a34a', 'bg' => '#f0fdf4', 'label' => translate('Bulk SMS'),          'desc' => translate('Send targeted SMS campaigns to thousands of contacts instantly.')],
                        ['icon' => 'bi-envelope-paper',  'color' => '#0891b2', 'bg' => '#ecfeff', 'label' => translate('Bulk Email'),        'desc' => translate('Run professional email campaigns with tracking and analytics.')],
                        ['icon' => 'bi-rocket-takeoff',  'color' => '#dc2626', 'bg' => '#fff1f2', 'label' => translate('Social Media Boost'),'desc' => translate('Buy followers, likes, and views for all major social platforms.')],
                    ];
                    @endphp
                    @foreach($features as $f)
                    <div class="col-md-6">
                        <div class="d-flex gap-3 align-items-start p-3 rounded-3" style="background:{{ $f['bg'] }};">
                            <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                style="width:38px;height:38px;background:{{ $f['color'] }}20;">
                                <i class="bi {{ $f['icon'] }}" style="color:{{ $f['color'] }};font-size:18px;"></i>
                            </span>
                            <div>
                                <p class="fw-semibold mb-1 fs-14">{{ $f['label'] }}</p>
                                <p class="text-muted fs-13 mb-0">{{ $f['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- How to subscribe steps --}}
        <div class="i-card mt-4">
            <h5 class="fw-bold mb-4">
                <i class="bi bi-map me-2" style="color:#7c2d3e;"></i>
                {{ translate('How to Get Started') }}
            </h5>
            <div class="row g-3">
                @php
                $steps = [
                    ['n'=>'01','title'=>translate('Fund Your Wallet'),'desc'=>translate('Go to Deposit and add funds to your account wallet using your preferred payment method.'),'icon'=>'bi-wallet2','route'=>route('user.deposit.create'),'cta'=>translate('Add Funds')],
                    ['n'=>'02','title'=>translate('Choose a Plan'),'desc'=>translate('Browse available subscription plans and pick the one that matches your usage needs.'),'icon'=>'bi-card-list','route'=>route('user.plan'),'cta'=>translate('View Plans')],
                    ['n'=>'03','title'=>translate('Activate & Use'),'desc'=>translate('Once subscribed, all features are immediately unlocked — create chatbots, send campaigns, and boost your social media.'),'icon'=>'bi-check-circle','route'=>null,'cta'=>null],
                ];
                @endphp
                @foreach($steps as $step)
                <div class="col-md-4">
                    <div class="p-3 rounded-3 h-100 d-flex flex-column gap-2"
                        style="background:#fdf8f8;border:1px solid #f3e0e0;">
                        <span class="fw-black fs-32 lh-1" style="color:#7c2d3e;opacity:.15;">{{ $step['n'] }}</span>
                        <i class="bi {{ $step['icon'] }} fs-22" style="color:#7c2d3e;"></i>
                        <p class="fw-semibold mb-1 fs-15">{{ $step['title'] }}</p>
                        <p class="text-muted fs-13 flex-grow-1 mb-2">{{ $step['desc'] }}</p>
                        @if($step['route'])
                        <a href="{{ $step['route'] }}" class="i-btn btn--sm capsuled"
                            style="background:#7c2d3e;color:#fff;border:none;width:fit-content;">
                            {{ $step['cta'] }} <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@endsection
