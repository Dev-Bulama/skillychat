@extends('layouts.master')
@section('content')
<div class="row g-4">

    <div class="col-12">
        <div class="i-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="card--title mb-1">{{ translate('How Social Media Boost Works') }}</h4>
                    <p class="text-muted mb-0 fs-14">{{ translate('Everything you need to know about ordering engagement services.') }}</p>
                </div>
                <a href="{{ route('user.smm.index') }}" class="i-btn btn--sm btn--primary">
                    <i class="bi bi-grid me-1"></i>{{ translate('Browse Services') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Steps --}}
    @foreach([
        ['bi-search', 'Step 1: Browse Services', 'Visit the Social Media Boost page and filter by platform (Instagram, TikTok, YouTube, etc.) or service type (followers, likes, views, subscribers). Each service card shows the price per 1,000, min/max quantity, and estimated delivery time.'],
        ['bi-cart-plus', 'Step 2: Place Your Order', 'Click "Order Now" on any service. Enter the target URL or username for the post/profile you want to boost, then enter the quantity. The system will show you the exact cost before you confirm.'],
        ['bi-wallet', 'Step 3: Payment from Wallet', 'Your order amount is deducted from your platform wallet instantly. Make sure you have sufficient balance before ordering. Top up your wallet via the Deposit section if needed.'],
        ['bi-send', 'Step 4: Order Processing', 'Your order is sent to our SMM provider. You\'ll see the status change from "Pending" to "Processing". The provider begins delivering your engagement.'],
        ['bi-check-circle', 'Step 5: Track & Complete', 'Monitor your order status in "My Orders". Once fully delivered, the status changes to "Completed". You can see start count and remaining count in the order detail page.'],
    ] as [$icon, $title, $desc])
    <div class="col-md-6 col-xl-4">
        <div class="i-card h-100 border">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="icon-btn icon-btn-md primary circle">
                    <i class="bi {{ $icon }} fs-20"></i>
                </span>
                <h6 class="mb-0 fw-semibold">{{ translate($title) }}</h6>
            </div>
            <p class="text-muted fs-14 mb-0">{{ translate($desc) }}</p>
        </div>
    </div>
    @endforeach

    {{-- Disclaimer box --}}
    <div class="col-12">
        <div class="i-card border" style="border-left:4px solid #ffc107 !important;">
            <h5 class="card--title mb-3">
                <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                {{ translate('Important Disclaimers') }}
            </h5>
            <div class="row g-3 fs-14 text-muted">
                <div class="col-md-6">
                    <i class="bi bi-x-circle text-danger me-2"></i>
                    {{ translate('We do NOT guarantee organic, real, or permanent followers/views. Engagement is delivered by third-party providers.') }}
                </div>
                <div class="col-md-6">
                    <i class="bi bi-shield-x text-danger me-2"></i>
                    {{ translate('Do not use these services if they violate the terms of service of the target platform.') }}
                </div>
                <div class="col-md-6">
                    <i class="bi bi-clock text-warning me-2"></i>
                    {{ translate('Delivery times are estimates only and may vary based on provider load and service type.') }}
                </div>
                <div class="col-md-6">
                    <i class="bi bi-lock text-primary me-2"></i>
                    {{ translate('Your account must be Public during the delivery period. Private accounts cannot receive engagements.') }}
                </div>
                <div class="col-md-6">
                    <i class="bi bi-arrow-repeat text-primary me-2"></i>
                    {{ translate('If your order fails, contact support. Refunds are processed back to your wallet.') }}
                </div>
                <div class="col-md-6">
                    <i class="bi bi-person-check text-success me-2"></i>
                    {{ translate('Do not change your username or delete the target post/profile during delivery.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- FAQ --}}
    <div class="col-12">
        <div class="i-card">
            <h5 class="card--title mb-4">{{ translate('Frequently Asked Questions') }}</h5>
            <div class="accordion" id="faqAccordion">
                @foreach([
                    ['How long does delivery take?', 'Delivery time varies by service and provider. Each service card shows an estimated delivery window. Most services begin within a few hours.'],
                    ['Can I order for a private account?', 'No. Your account must be set to Public for engagement to be delivered. Switch to public before placing the order.'],
                    ['What happens if my order fails?', 'If the provider returns an error, your order status will show as "Failed". Contact support and we will review and refund your wallet if applicable.'],
                    ['Will the followers drop off?', 'Some drop-off is normal with engagement services. We cannot control retention — this depends on the specific service and provider.'],
                    ['Can I cancel an order?', 'Orders in Pending status may be refunded. Once an order reaches Processing status, cancellation depends on the provider\'s policies.'],
                    ['Is it safe for my account?', 'Use these services at your own risk. We recommend ordering smaller quantities first to test compatibility with your account.'],
                ] as [$q, $a])
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#faq{{ $loop->index }}">
                            {{ translate($q) }}
                        </button>
                    </h2>
                    <div id="faq{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted fs-14">
                            {{ translate($a) }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- CTA --}}
    <div class="col-12">
        <div class="i-card text-center py-4" style="background:linear-gradient(135deg,#6c63ff15,#6c63ff05);">
            <h5 class="mb-2">{{ translate('Ready to Boost?') }}</h5>
            <p class="text-muted mb-3">{{ translate('Browse available services and place your first order today.') }}</p>
            <a href="{{ route('user.smm.index') }}" class="i-btn btn--md btn--primary capsuled">
                <i class="bi bi-rocket-takeoff me-2"></i>{{ translate('Get Started') }}
            </a>
        </div>
    </div>

</div>
@endsection
