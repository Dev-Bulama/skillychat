@php use Illuminate\Support\Arr; @endphp
@extends('layouts.master')
@push('style-include')
    <link nonce="{{ csp_nonce() }}" href="{{asset('assets/global/css/datepicker/daterangepicker.css')}}" rel="stylesheet" type="text/css"/>
@endpush
@section('content')

@php

        $user             = auth_user('web')->load(['runningSubscription','runningSubscription.package']);
        $subscription     = $user->runningSubscription;
        $remainingToken   = $subscription ? $subscription->remaining_word_balance : 0;
        $remainingProfile = $subscription ? $subscription->total_profile : 0;
        $remainingPost    = $subscription ? $subscription->remaining_post_balance : 0;
        $accessPlatforms         = (array) ($subscription ? @$subscription->package->social_access->platform_access : []);
        $platforms = get_platform()
                        ->whereIn('id', $accessPlatforms )
                        ->where("status",App\Enums\StatusEnum::true->status())
                        ->where("is_integrated",App\Enums\StatusEnum::true->status());

        $subscriptionDetails = collect([
            'remaining_word'    => $remainingToken,
            'remaining_profile' => $remainingProfile,
            'remaining_post'    => $remainingPost,
            'total_patforms'   => count($accessPlatforms)])->mapWithKeys(fn($value,$key) :array =>  [k2t($key) => $value])->toArray();
        if( $remainingToken == App\Enums\PlanDuration::value('UNLIMITED')) unset($subscriptionDetails['remaining_word']);
        if( $remainingPost == App\Enums\PlanDuration::value('UNLIMITED')) unset($subscriptionDetails['remaining_profile']);
        $socialMediaEnabled = $socialMediaEnabled ?? \App\Models\FeatureFlag::enabled('social_media');
@endphp

@php
    $totalChatbots = Arr::get($data['chatbot_report'] ?? [], 'total_chatbots', 0);
    $isNewUser     = $totalChatbots == 0 && Arr::get($data, 'total_post', 0) == 0;
    $userName      = auth_user('web')?->name ?? 'there';
    $firstName     = explode(' ', $userName)[0];
@endphp

<div id="overlay" class="overlay"></div>
<button id="right-sidebar-btn" class="right-sidebar-btn fs-20">
    <i class="bi bi-activity"></i>
</button>

{{-- ══════════════════════════════════════════════════════
     WELCOME BANNER + QUICK ACTIONS
     ══════════════════════════════════════════════════════ --}}
<div class="row g-4 mb-2">
    <div class="col-12">
        <div class="i-card" style="background:linear-gradient(135deg,#3b0764 0%,#6d28d9 60%,#7c3aed 100%);color:#fff;border:none;overflow:hidden;position:relative;">
            {{-- decorative blobs --}}
            <div style="position:absolute;width:220px;height:220px;background:rgba(255,255,255,.06);border-radius:50%;top:-60px;right:-60px;pointer-events:none;"></div>
            <div style="position:absolute;width:140px;height:140px;background:rgba(255,255,255,.04);border-radius:50%;bottom:-40px;right:120px;pointer-events:none;"></div>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3" style="position:relative;z-index:1;">
                <div>
                    <p class="mb-1 fs-14" style="opacity:.8;">
                        {{ $isNewUser ? translate("Welcome aboard 👋") : translate("Good to see you again 👋") }}
                    </p>
                    <h3 class="fw-bold mb-1" style="color:#fff;">{{ $firstName }}!</h3>
                    <p class="mb-0 fs-14" style="opacity:.75;max-width:480px;">
                        @if($isNewUser)
                            {{ translate("Your account is ready. Pick something below to get started — it only takes a minute.") }}
                        @else
                            {{ translate("Here's a quick look at your workspace. Jump right back in.") }}
                        @endif
                    </p>
                </div>
                @if(!$subscription)
                <a href="{{ route('user.plan') }}" class="i-btn btn--md"
                    style="background:#fff;color:#6d28d9;font-weight:600;border:none;flex-shrink:0;">
                    <i class="bi bi-lightning-charge me-1"></i>{{ translate('Upgrade Plan') }}
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Quick Action Cards ──────────────────────────────── --}}
    @php
        $quickActions = [
            [
                'icon'    => 'bi-robot',
                'color'   => '#6d28d9',
                'bg'      => '#f3f0ff',
                'label'   => translate('AI Chatbot'),
                'desc'    => translate('Build & deploy a smart chatbot for your site.'),
                'cta'     => translate('Create Chatbot'),
                'route'   => route('user.chatbot.create'),
                'badge'   => $totalChatbots > 0 ? $totalChatbots . ' active' : null,
            ],
            [
                'icon'    => 'bi-rocket-takeoff',
                'color'   => '#dc2626',
                'bg'      => '#fff1f2',
                'label'   => translate('Social Media Boost'),
                'desc'    => translate('Get followers, likes & views instantly.'),
                'cta'     => translate('Browse Services'),
                'route'   => route('user.smm.index'),
                'badge'   => null,
            ],
            [
                'icon'    => 'bi-envelope-paper',
                'color'   => '#0891b2',
                'bg'      => '#ecfeff',
                'label'   => translate('Bulk Email'),
                'desc'    => translate('Send email campaigns to your contacts.'),
                'cta'     => translate('Create Campaign'),
                'route'   => route('user.bulk-email.create'),
                'badge'   => null,
            ],
            [
                'icon'    => 'bi-phone',
                'color'   => '#16a34a',
                'bg'      => '#f0fdf4',
                'label'   => translate('Bulk SMS'),
                'desc'    => translate('Reach your audience with targeted SMS.'),
                'cta'     => translate('Send SMS'),
                'route'   => route('user.bulk-sms.create'),
                'badge'   => null,
            ],
            [
                'icon'    => 'bi-wallet2',
                'color'   => '#b45309',
                'bg'      => '#fffbeb',
                'label'   => translate('Fund Wallet'),
                'desc'    => translate('Top up to place orders and pay for services.'),
                'cta'     => translate('Add Funds'),
                'route'   => route('user.deposit.create'),
                'badge'   => $subscription ? translate('Plan Active') : null,
            ],
        ];
    @endphp

    @foreach($quickActions as $action)
    <div class="col-xl col-md-4 col-sm-6 col-12">
        <a href="{{ $action['route'] }}" class="text-decoration-none d-block h-100">
            <div class="i-card h-100 d-flex flex-column gap-2 position-relative"
                style="border-left:4px solid {{ $action['color'] }};transition:transform .18s,box-shadow .18s;"
                onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.10)'"
                onmouseout="this.style.transform='';this.style.boxShadow=''">
                @if($action['badge'])
                <span class="badge position-absolute top-0 end-0 m-2 fs-11"
                    style="background:{{ $action['color'] }};color:#fff;">{{ $action['badge'] }}</span>
                @endif
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                        style="width:40px;height:40px;background:{{ $action['bg'] }};">
                        <i class="bi {{ $action['icon'] }} fs-18" style="color:{{ $action['color'] }};"></i>
                    </span>
                    <span class="fw-semibold fs-15" style="color:#1e1b4b;">{{ $action['label'] }}</span>
                </div>
                <p class="fs-13 text-muted mb-2 flex-grow-1">{{ $action['desc'] }}</p>
                <span class="fs-13 fw-semibold d-flex align-items-center gap-1" style="color:{{ $action['color'] }};">
                    {{ $action['cta'] }} <i class="bi bi-arrow-right fs-12"></i>
                </span>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- ── Getting Started Checklist (new users only) ────────── --}}
@if($isNewUser)
<div class="row g-4 mb-2">
    <div class="col-12">
        <div class="i-card" style="border:2px dashed #c4b5fd;">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                    style="width:44px;height:44px;background:#f3f0ff;">
                    <i class="bi bi-map fs-20" style="color:#6d28d9;"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0">{{ translate('Getting Started') }}</h5>
                    <p class="fs-13 text-muted mb-0">{{ translate('Follow these steps to make the most of your account.') }}</p>
                </div>
            </div>
            <div class="row g-3">
                @php
                $steps = [
                    [
                        'num'   => '01',
                        'color' => '#6d28d9',
                        'bg'    => '#f3f0ff',
                        'title' => translate('Fund Your Wallet'),
                        'desc'  => translate('Add credits to your wallet to start ordering services and sending campaigns.'),
                        'link'  => route('user.deposit.create'),
                        'cta'   => translate('Add Funds'),
                        'icon'  => 'bi-wallet2',
                    ],
                    [
                        'num'   => '02',
                        'color' => '#0891b2',
                        'bg'    => '#ecfeff',
                        'title' => translate('Create Your First Chatbot'),
                        'desc'  => translate('Set up an AI chatbot to handle customer queries 24/7 on your website.'),
                        'link'  => route('user.chatbot.create'),
                        'cta'   => translate('Create Chatbot'),
                        'icon'  => 'bi-robot',
                    ],
                    [
                        'num'   => '03',
                        'color' => '#dc2626',
                        'bg'    => '#fff1f2',
                        'title' => translate('Boost Your Social Media'),
                        'desc'  => translate('Buy real followers, likes, and views for Instagram, TikTok, YouTube, and more.'),
                        'link'  => route('user.smm.index'),
                        'cta'   => translate('Browse Services'),
                        'icon'  => 'bi-rocket-takeoff',
                    ],
                    [
                        'num'   => '04',
                        'color' => '#16a34a',
                        'bg'    => '#f0fdf4',
                        'title' => translate('Launch a Campaign'),
                        'desc'  => translate('Send bulk SMS or email campaigns to your audience in one click.'),
                        'link'  => route('user.bulk-email.index'),
                        'cta'   => translate('Start Campaign'),
                        'icon'  => 'bi-megaphone',
                    ],
                ];
                @endphp
                @foreach($steps as $step)
                <div class="col-xl-3 col-md-6">
                    <div class="p-3 rounded-3 h-100 d-flex flex-column gap-2"
                        style="background:{{ $step['bg'] }};border:1px solid rgba(0,0,0,.06);">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fw-black fs-28 lh-1" style="color:{{ $step['color'] }};opacity:.18;font-variant-numeric:tabular-nums;min-width:38px;">{{ $step['num'] }}</span>
                            <i class="bi {{ $step['icon'] }} fs-22" style="color:{{ $step['color'] }};"></i>
                        </div>
                        <h6 class="fw-semibold mb-1">{{ $step['title'] }}</h6>
                        <p class="fs-13 text-muted flex-grow-1 mb-2">{{ $step['desc'] }}</p>
                        <a href="{{ $step['link'] }}" class="i-btn btn--sm capsuled"
                            style="background:{{ $step['color'] }};color:#fff;border:none;width:fit-content;">
                            {{ $step['cta'] }} <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col">
        <div class="row g-4">

            @if($socialMediaEnabled)
            <div class="col-xl-6">
                <div class="i-card h-550">
                    <h4 class="card--title mb-4">
                         {{translate('Connected Social Accounts')}}
                    </h4>
                    <div class="row g-3">
                       @forelse(Arr::get($data['account_report'] ,'accounts_by_platform',[]) as $platform)
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="i-card no-border p-0 border position-relative bg--light">
                                    <div class="shape-one">
                                        <svg width="65" height="65" viewBox="0 0 65 65" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M52.3006 64.8958L64.4805 64.9922L64.9908 0.510364L0.508992 1.7845e-05L0.412593 12.1799L35.5193 12.4578C45.016 12.533 52.6536 20.2924 52.5784 29.789L52.3006 64.8958Z"
                                                fill="none" />
                                        </svg>
                                    </div>
                                    <div class="shape-two">
                                        <svg width="65" height="65" viewBox="0 0 65 65" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M52.3006 64.8958L64.4805 64.9922L64.9908 0.510364L0.508992 1.7845e-05L0.412593 12.1799L35.5193 12.4578C45.016 12.533 52.6536 20.2924 52.5784 29.789L52.3006 64.8958Z"
                                                fill="none" />
                                        </svg>
                                    </div>
                                    <span class="icon-image">
                                        <img src="{{imageUrl(@$platform->file,'platform',true)}}"
                                            alt="{{@$platform->name.'.jpg'}}" />
                                    </span>
                                    <div class="p-3">
                                        <h5 class="card--title-sm">
                                            {{$platform->name}}
                                        </h5>
                                    </div>
                                    <div class="p-3 border-top">
                                        <p class="card--title-sm mb-1">
                                            {{$platform->accounts_count}}
                                        </p>
                                        <p class="mb-3 fs-14">
                                              {{translate('Total Posts')}}
                                        </p>
                                        <a href="{{route('user.social.account.create',['platform' => $platform->slug])}}" class="i-btn btn--sm btn--outline capsuled w-100">
                                             <i class="bi bi-plus-lg"></i>
                                              {{translate('Create Account')}}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty

                             <div class="col-12">
                                  @include('admin.partials.not_found')
                             </div>

                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="i-card h-100">
                    <ul class="social-account-list-2 mb-2">

                        <li>
                            <a href="{{route('user.home')}}" class="{{!request()->input('platform') ? 'active' :''}}">
                                 {{translate('ALL')}}
                            </a>
                        </li>

                        @forelse ($platforms as $platform )
                            <li>
                                <a class="{{$platform->slug == request()->input('platform') ? 'active' :''}}" href="{{route('user.home',['platform' => $platform->slug])}}">
                                    {{$platform->name}}
                                </a>
                            </li>
                         @empty

                         @endforelse
                    </ul>

                    <div id="postReport"></div>

                </div>
            </div>
            @else
            {{-- Chatbot Analytics (shown when social media is disabled) --}}
            <div class="col-12">
                <div class="i-card">
                    <h4 class="card--title mb-4">
                        <i class="bi bi-robot me-2"></i>{{translate('Chatbot Analytics')}}
                    </h4>
                    <div class="row g-3">
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="i-card border overview-card p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-robot fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate('Total Chatbots')}}</p>
                                        <h6>{{Arr::get($data['chatbot_report'],'total_chatbots',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between">
                                    <a class="text--success" href="{{route('user.chatbot.index')}}">{{translate('View All')}}</a>
                                    <p class="mb-0 fs-14">{{translate('All time')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="i-card border overview-card p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-chat-dots fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate('Total Conversations')}}</p>
                                        <h6>{{Arr::get($data['chatbot_report'],'total_conversations',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between">
                                    <p class="mb-0 fs-14">{{translate('All time')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="i-card border overview-card p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-chat-text fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate('Conversations This Month')}}</p>
                                        <h6>{{Arr::get($data['chatbot_report'],'conversations_month',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between">
                                    <p class="mb-0 fs-14">{{translate('This month')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="i-card border overview-card p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-envelope-paper fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate('Messages This Month')}}</p>
                                        <h6>{{Arr::get($data['chatbot_report'],'messages_month',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between">
                                    <p class="mb-0 fs-14">{{translate('This month')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="i-card border overview-card p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-people fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate('Unique Visitors')}}</p>
                                        <h6>{{Arr::get($data['chatbot_report'],'unique_visitors',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between">
                                    <p class="mb-0 fs-14">{{translate('All time')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="i-card border overview-card p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-headset fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate('Human Takeover Requests')}}</p>
                                        <h6>{{Arr::get($data['chatbot_report'],'human_requests',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between">
                                    <a class="text--success" href="{{route('user.live-agent.dashboard')}}">{{translate('View')}}</a>
                                    <p class="mb-0 fs-14">{{translate('Pending')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="i-card border overview-card p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-chat-square-text fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate('Total Messages')}}</p>
                                        <h6>{{Arr::get($data['chatbot_report'],'total_messages',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between">
                                    <p class="mb-0 fs-14">{{translate('All time')}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($socialMediaEnabled)
            <div class="col-12">
                <div class="i-card h-100">
                    <div class="row align-items-center g-2 mb-4">
                        <div class="col-md-9">
                            <h4 class="card--title">
                               {{translate('Overview')}}
                            </h4>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-sm-6">
                            <div class="i-card border overview-card p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-graph-up fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">
                                            {{translate("Total Post")}}
                                        </p>
                                        <h6>
                                            {{Arr::get($data,'total_post',0)}}
                                        </h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between">

                                     <a class="text--success" href="{{route('user.social.post.list')}}">
                                          {{translate('View All')}}
                                     </a>

                                    <p class="mb-0 fs-14"> {{translate('This year')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-sm-6">
                            <div class="i-card border p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-calendar-event fs-30"></i>
                                    </div>

                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate("Pending Post")}}</p>
                                        <h6>{{Arr::get($data,'pending_post',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between">
                                    <a class="text--success" href="{{route('user.social.post.list',['status' =>App\Enums\PostStatus::PENDING->value ])}}">
                                        {{translate('View All')}}
                                   </a>
                                    <p class="mb-0 fs-14">{{translate('This year')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-sm-6">
                            <div class="i-card border p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-clock fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate("Schedule Post")}}</p>
                                        <h6>{{Arr::get($data,'schedule_post',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between flex-wrap">
                                    <a class="text--success" href="{{route('user.social.post.list',['status' =>App\Enums\PostStatus::SCHEDULE->value ])}}">
                                        {{translate('View All')}}
                                   </a>
                                    <p class="mb-0 fs-14">{{translate('This year')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-sm-6">
                            <div class="i-card border p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-check-circle fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate("Success Post")}}</p>
                                        <h6>{{Arr::get($data,'success_post',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between flex-wrap">
                                    <a class="text--success" href="{{route('user.social.post.list',['status' =>App\Enums\PostStatus::SUCCESS->value ])}}">
                                        {{translate('View All')}}
                                    </a>
                                    <p class="mb-0 fs-14"> {{translate('This year')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-sm-6">
                            <div class="i-card border p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-x-circle fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate("Failed Post")}}</p>
                                        <h6>{{Arr::get($data,'failed_post',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between flex-wrap">
                                    <a class="text--success" href="{{route('user.social.post.list',['status' =>App\Enums\PostStatus::FAILED->value ])}}">
                                        {{translate('View All')}}
                                   </a>
                                    <p class="mb-0 fs-14"> {{translate('This year')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-sm-6">
                            <div class="i-card border p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-person-circle fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate("Total Account")}}</p>
                                        <h6>{{Arr::get($data['account_report'],'total_account',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between flex-wrap">
                                    <a class="text--success" href="{{route('user.social.account.list')}}">
                                        {{translate('View All')}}
                                   </a>
                                    <p class="mb-0 fs-14"> {{translate('This year')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-sm-6">
                            <div class="i-card border p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-person-check fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate("Active Account")}}</p>
                                        <h6>{{Arr::get($data['account_report'],'active_account',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between flex-wrap">
                                    <a class="text--success" href="{{route('user.social.account.list',['status' =>  App\Enums\StatusEnum::true->status()])}}">
                                        {{translate('View All')}}
                                   </a>
                                    <p class="mb-0 fs-14"> {{translate('This year')}}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-3 col-xl-4 col-lg-4 col-sm-6">
                            <div class="i-card border p-0">
                                <div class="p-3">
                                    <div class="icon graident-icon mb-3">
                                        <i class="bi bi-person-x fs-30"></i>
                                    </div>
                                    <div class="content">
                                        <p class="card--title-sm mb-1">{{translate("Inactive Account")}}</p>
                                        <h6>{{Arr::get($data['account_report'],'inactive_account',0)}}</h6>
                                    </div>
                                </div>
                                <div class="footer border-top d-flex justify-content-between flex-wrap">
                                    <a class="text--success" href="{{route('user.social.account.list',['status' =>  App\Enums\StatusEnum::false->status()])}}">
                                        {{translate('View All')}}
                                   </a>
                                    <p class="mb-0 fs-14">
                                         {{translate('This year')}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="col-12">
                <div class="i-card-md card-height-100">
                    <div class="card-header">
                        <h4 class="card--title">
                            {{translate("Latest Transaction Log")}}
                        </h4>
                    </div>

                    <div class="card-body px-0">
                        <div class="table-accordion">
                            @php
                            $reports = Arr::get($data,'latest_transactiions',null);
                            @endphp
                            @if($reports && $reports->count() > 0)
                            <div class="accordion" id="wordReports">
                                @forelse(Arr::get($data,'latest_transactiions',[]) as $report)
                                <div class="accordion-item">
                                    <div class="accordion-header">
                                        <div class="accordion-button collapsed" role="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{$report->id}}" aria-expanded="false"
                                            aria-controls="collapse{{$report->id}}">
                                            <div class="row align-items-center w-100 gy-4 gx-sm-3 gx-0">
                                                <div class="col-lg-3 col-sm-4 col-12">
                                                    <div class="table-accordion-header transfer-by">
                                                        <span class="icon-btn icon-btn-sm primary circle">
                                                            <i class="bi bi-file-text"></i>
                                                        </span>
                                                        <div>
                                                            <h6>
                                                                {{translate("Trx Code")}}
                                                            </h6>
                                                            <p> {{$report->trx_code}}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-sm-4 col-6 text-lg-center text-sm-center text-start">
                                                    <div class="table-accordion-header">
                                                        <h6>
                                                            {{translate("Date")}}
                                                        </h6>
                                                        <p>
                                                            {{ get_date_time($report->created_at) }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-sm-4 col-6 text-lg-center text-sm-end text-end">
                                                    <div class="table-accordion-header">
                                                        <h6>
                                                            {{translate("Balance")}}
                                                        </h6>

                                                        <p
                                                            class='text--{{$report->trx_type == App\Models\Transaction::$PLUS ? "success" :"danger" }}'>
                                                            <i class='bi bi-{{$report->trx_type == App\Models\Transaction::$PLUS ? "plus" :"dash" }}'></i>
                                                            {{num_format($report->amount,$report->currency)}}
                                                        </p>

                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-sm-4 col-6 text-lg-center text-start">
                                                    <div class="table-accordion-header">
                                                        <h6>{{translate("Post Balance")}}</h6>
                                                        <p>
                                                            {{@num_format(
                                                                number : $report->post_balance??0,
                                                                calC   : true
                                                            )}}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-sm-4 col-6 text-lg-end text-md-center text-end">
                                                    <div class="table-accordion-header">
                                                        <h6>{{translate("Remark")}}</h6>
                                                        <p>
                                                            {{k2t($report->remarks)}}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="collapse{{$report->id}}" class="accordion-collapse collapse"
                                        data-bs-parent="#wordReports">
                                        <div class="accordion-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item">
                                                    <h6 class="title">
                                                        {{translate("Report Information")}}
                                                    </h6>
                                                    <p class="value">
                                                        {{$report->details}}
                                                    </p>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                @endforelse
                            </div>
                            @else
                                @include('admin.partials.not_found',['custom_message' => "No Reports found!!"])
                            @endif

                        </div>

                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="i-card-md card-height-100">
                    <div class="card-header">
                        <h4 class="card--title">
                            {{translate("Latest Subscription Log")}}
                        </h4>

                    </div>

                    <div class="card-body px-0">
                        <div class="table-accordion">
                            @php
                            $reports = Arr::get($data,'subscription_log',null);
                            @endphp

                            @if($reports && $reports->count() > 0)
                            <div class="accordion" id="wordReports-2">
                                @forelse($reports as $report)
                                <div class="accordion-item">
                                    <div class="accordion-header">
                                        <div class="accordion-button collapsed" role="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{$report->id}}" aria-expanded="false"
                                            aria-controls="collapse{{$report->id}}">
                                            <div class="row align-items-center w-100 gy-4 gx-sm-3 gx-0">
                                                <div class="col-lg-2 col-sm-4 col-12">
                                                    <div class="table-accordion-header transfer-by">
                                                        <span class="icon-btn icon-btn-sm primary circle">
                                                            <i class="bi bi-file-text"></i>
                                                        </span>
                                                        <div>
                                                            <h6>
                                                                {{translate("TRX Code")}}
                                                            </h6>
                                                            <p> {{$report->trx_code}}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-sm-4 col-6 text-lg-center text-sm-center text-start">
                                                    <div class="table-accordion-header">
                                                        <h6>
                                                            {{translate("Expired In")}}
                                                        </h6>
                                                        <p>
                                                            @if($report->expired_at)
                                                            {{ get_date_time($report->expired_at,'d M, Y') }}
                                                            @else
                                                            --
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-sm-4 col-6 text-lg-center text-sm-end text-end">
                                                    <div class="table-accordion-header">
                                                        <h6>
                                                            {{translate("Package")}}
                                                        </h6>
                                                        <p>
                                                            {{@$report->package->title}}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-sm-4 col-6 text-lg-center text-start">
                                                    <div class="table-accordion-header">
                                                        <h6>
                                                            {{translate("Status")}}
                                                        </h6>
                                                        @php echo (subscription_status($report->status)) @endphp
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-sm-4 col-6 text-sm-center text-end">
                                                    <div class="table-accordion-header">
                                                        <h6>{{translate("Payment Amount")}}</h6>
                                                        <p>
                                                            {{@num_format(
                                                        number : $report->payment_amount??0,
                                                        calC   : true
                                                    )}}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="col-lg-2 col-sm-4 col-6 text-sm-end text-start">
                                                    <div class="table-accordion-header">
                                                        <h6>{{translate("Date")}}</h6>
                                                        <p>
                                                            @if($report->created_at)
                                                            {{ get_date_time($report->created_at) }}
                                                            @else
                                                            --
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="collapse{{$report->id}}" class="accordion-collapse collapse"
                                        data-bs-parent="#wordReports-2">
                                        <div class="accordion-body">
                                            <ul class="list-group list-group-flush">
                                                @php
                                                $informations = [
                                                    "AI_word_balance"          => $report->word_balance,
                                                    "remaining_word_balance"   => $report->remaining_word_balance,
                                                    "carried_word_balance"     => $report->carried_word_balance,
                                                    "total_social_profile"     => $report->total_profile,
                                                    "carried_profile_balance"  => $report->carried_profile,
                                                    "social_post_balance"      => $report->post_balance,
                                                    "remaining_post_balance"   => $report->remaining_post_balance,
                                                    "carried_post_balance"     => $report->carried_post_balance,
                                                ];
                                                @endphp

                                                @foreach ($informations as $key => $val)
                                                    <li class="list-group-item">
                                                        <h6 class="title">
                                                            {{k2t($key)}}
                                                        </h6>
                                                        <p class="value">
                                                            {{$val == App\Enums\PlanDuration::UNLIMITED->value ? App\Enums\PlanDuration::UNLIMITED->name : $val }}
                                                        </p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                @endforelse
                            </div>
                            @else
                            @include('admin.partials.not_found',['custom_message' => "No Reports found!!"])
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-auto right-side-col" >
        <div class="i-card mb-4 sidebar-post">
            <h4 class="card--title mb-20">{{translate('Latest Post')}}</h4>
            @php
                $latestPost = Arr::get($data,'latest_post',collect([]));
            @endphp

            @if( $latestPost->count() > 0)

                <div class="swiper latest-post-slider">

                    <div class="swiper-wrapper">
                        @foreach ($latestPost as $post )
                            <div class="swiper-slide">
                                <div>

                                    @if($post->file->count() > 0)
                                        <div class="latest-post-banner mb-3">

                                                @php
                                                    $fileURL = $post->file->count() > 0
                                                                    ? imageURL($post->file->first(),"post",true)
                                                                    : get_default_img();
                                                @endphp

                                                @if(!isValidVideoUrl($fileURL))
                                                    <img src="{{ $fileURL}}" class="radius-8 mb-3" alt="post.jpg">
                                                @else
                                                    <video  width="150" controls>
                                                        <source src="{{$fileURL}}">
                                                    </video>
                                                @endif

                                        </div>
                                    @endif

                                    @if($post->content)
                                        <h6 class="latest-post-title mb-1">
                                            {{$post->content}}
                                        </h6>
                                    @endif
                                    @if($post->link)
                                        <a target="_blank" href="{{$post->link}}">
                                            {{translate('Link')}}
                                        </a>
                                    @endif
                                    <div class="d-flex mb-1">
                                        @if(@$post->account->account_information->link)
                                            <a  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('Account name')}}"  target="_blank" href="{{@$post->account->account_information->link}}">
                                                #{{ @$post->account->account_information->name}}
                                            </a>
                                        @else
                                            {{ @$post->account->account_information->name}}
                                        @endif

                                        @if(@$post->account->platform)
                                        <a href="{{route('user.social.account.list',['platform' => $platform->slug])}}"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('Platform')}}">
                                                #{{@$post->account->platform->name}}
                                        </a>
                                        @endif
                                    </div>
                                    <div class="date mb-3">
                                        <span class="fs-14 text--light">{{get_date_time($post->created_at,"F j, Y")}}</span> <span class="fs-12 text--light">{{get_date_time($post->created_at,"g a")}}</span>
                                    </div>
                                    <a href="{{route('user.social.post.show',['uid' => $post->uid])}}" class="i-btn btn--primary btn--lg capsuled w-100">
                                        {{translate('View Post')}}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="latest-post-pagination"></div>
            @else
                <div>
                    @include('admin.partials.not_found')
                </div>
            @endif
        </div>

        <div class="i-card upgrade-card mb-4">
            @if($subscription &&   $subscription->package)
                <h4 class="card--title text-white">
                     {{$subscription->package->title}}
                </h4>
                <p>
                    {!!$subscription->package->description!!}
                </p>
            @endif
            <a href="{{route('user.plan')}}" class="i-btn btn--md btn--white capsuled mx-auto">
                @if($subscription)
                    {{translate('Upgrade Now')}}
                @else
                   {{translate('Subscribe Now')}}
                @endif
            </a>
        </div>

        <div class="i-card mb-4">
            <div class="card-header mb-20">
                <h4 class="card--title">
                    {{ translate("Latest Activity ") }}
                </h4>
            </div>

            @php
                $activities =  Arr::get($data,'latest_activities',collect([]));
            @endphp

            <div class="card-body">
                <ul class="share-card" data-simplebar>
                    @forelse ($activities as $activitiy)
                        <li class="mb-3 fs-15"><span class="me-1 text--primary"><i class="bi bi-card-text"></i></span>
                            {{ $activitiy->details}}
                        </li>
                    @empty
                        <li class="mb-3 fs-15">
                            {{ translate('No activities found!!') }}
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="i-card">
            <div class="card-header mb-20">
                <h4 class="card--title ">
                    {{translate("Subscription Specification")}}
                </h4>
            </div>

            <div class="card-body">
                <div id="subscriptionChart" class="subscription-chart">
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@push('script-include')
    <script src="{{asset('assets/global/js/apexcharts.js')}}"></script>
    <script nonce="{{ csp_nonce() }}" src="{{asset('assets/global/js/datepicker/moment.min.js')}}"></script>
    <script nonce="{{ csp_nonce() }}" src="{{asset('assets/global/js/datepicker/daterangepicker.min.js')}}"></script>
    <script nonce="{{ csp_nonce() }}" src="{{asset('assets/global/js/datepicker/init.js')}}"></script>
@endpush

@push('script-push')

<script nonce="{{ csp_nonce() }}">
  "use strict";

    // Get current theme and direction
    function getCurrentTheme() {
        return document.documentElement.getAttribute('data-bs-theme') || 'light';
    }

    function getCurrentDirection() {
        return document.documentElement.getAttribute('dir') || 'ltr';
    }

    function isRTL() {
        return getCurrentDirection() === 'rtl';
    }

    function isDarkMode() {
        return getCurrentTheme() === 'dark';
    }

    // Get theme-aware colors
    function getThemeColors() {
        const isDark = isDarkMode();
        
        if (isDark) {
            return [
                "var(--bs-primary)",
                "var(--bs-secondary)", 
                "var(--bs-warning)",
                "var(--bs-info)",
                "var(--bs-danger)"
            ];
        } else {
            return [
                "var(--color-primary)",
                "var(--color-secondary)",
                "var(--color-warning)",
                "var(--color-info)",
                "var(--color-danger)"
            ];
        }
    }

    // Get text color based on theme
    function getTextColor() {
        return isDarkMode() ? '#ffffff' : '#373d3f';
    }

    // Get grid color based on theme
    function getGridColor() {
        return isDarkMode() ? '#404040' : '#f1f1f1';
    }


    var subscriptionValues = @json(array_values($subscriptionDetails));
    var subscriptionLabel = @json(array_keys($subscriptionDetails));

    var options = {
        series: subscriptionValues,
        chart: {
            type: "donut",
            width: "100%",
            nonce:"{{ csp_nonce() }}",
        },
        colors: getThemeColors(),
        labels: subscriptionLabel,
        plotOptions: {
             pie: {
                startAngle: isRTL() ? 270 : -90,
                endAngle: isRTL() ? -90 : 270
            }
        },
        dataLabels: {
            enabled: false
        },

        legend: {
             fontSize: '12px',
             position: 'bottom',
            labels: {
                colors: getTextColor()
            },
        },
    };

    var chart = new ApexCharts(document.querySelector("#subscriptionChart"), options);
    chart.render();


    var monthlyLabel = @json(array_keys($data['monthly_post_graph']));
    var accountValues = [];
    var totalPost     = @json(array_values($data['monthly_post_graph']));
    var pendigPost    = @json(array_values($data['monthly_pending_post']));
    var schedulePost  = @json(array_values($data['monthly_schedule_post']));
    var successPost   = @json(array_values($data['monthly_success_post']));
    var failedPost    = @json(array_values($data['monthly_failed_post']));

    var monthlyLabel = @json(array_keys($data['monthly_post_graph']));

    var options = {
        chart: {
            height: 410,
            type: "line",
            nonce:"{{ csp_nonce() }}",
            toolbar: {
                       show: false
                    }
        },
        dataLabels: {
            enabled: false,
        },
        colors: getThemeColors(),
        series: [{
                name: "{{ translate('Total Post') }}",
                data: totalPost,
            },
            {
                name: "{{ translate('Pending Post') }}",
                data: pendigPost,
            },
            {
                name: "{{ translate('Success Post') }}",
                data: successPost,
            },
            {
                name: "{{ translate('Schedule Post') }}",
                data: schedulePost,
            },
            {
                name: "{{ translate('Failed Post') }}",
                data: failedPost,
            },
        ],
        xaxis: {
            categories: monthlyLabel,
            labels: {
                style: {
                    colors: getTextColor(),
                    fontSize: '12px'
                }
            },
            axisBorder: {
                color: getGridColor()
            },
            axisTicks: {
                color: getGridColor()
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: getTextColor(),
                    fontSize: '12px'
                }
            }
        },
        grid: {
            borderColor: getGridColor(),
            strokeDashArray: 3
        },

        tooltip: {
            shared: false,
            intersect: true,
            theme: getCurrentTheme(),
            y: {
                formatter: function(value, {
                    series,
                    seriesIndex,
                    dataPointIndex,
                    w
                }) {
                    return parseInt(value);
                }
            }

        },
        markers: {
            size: 6,
        },
        stroke: {
            width: [4, 4],
            curve: 'smooth'
        },
        legend: {
            horizontalAlign: "center",
            offsetY: 5,
            labels: {
                colors: getTextColor()
            },
        },
    };

    var chart = new ApexCharts(document.querySelector("#postReport"), options);
    chart.render();

    var swiper = new Swiper(".latest-post-slider", {
        pagination: {
            el: ".latest-post-pagination",
            clickable: true,
        },
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        rtl: isRTL(),
        observer: true,
        observeParents: true,
    });

    $(".select2").select2({
         dir: getCurrentDirection(),
        theme: isDarkMode() ? 'default' : 'default', // You might want to add custom dark theme
        // Additional Select2 options for better RTL support
        language: {
            dir: getCurrentDirection()
        }
    });
</script>
@endpush
