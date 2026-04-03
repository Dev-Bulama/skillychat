@extends('admin.layouts.master')
@section('content')
<div class="i-card-md">
    <div class="card--header">
        <h4 class="card-title">{{ translate('SMM Boost — Setup Guide') }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.smm.providers') }}" class="i-btn btn--sm btn--primary">
                <i class="bi bi-plug me-1"></i>{{ translate('Manage Providers') }}
            </a>
            <a href="{{ route('admin.smm.services') }}" class="i-btn btn--sm btn--outline">
                <i class="bi bi-grid me-1"></i>{{ translate('Manage Services') }}
            </a>
        </div>
    </div>
    <div class="card-body">

        <div class="alert" style="background:#f0f4ff;border-left:4px solid #6c63ff;padding:16px;border-radius:8px;margin-bottom:24px;">
            <strong>{{ translate('What is Social Media Boost?') }}</strong><br>
            {{ translate('Social Media Boost lets your users purchase engagement services — followers, likes, views, subscribers, shares — for major platforms like Instagram, TikTok, YouTube, Facebook and Twitter through integrated SMM API providers.') }}
        </div>

        <div class="row g-4">

            {{-- Step 1 --}}
            <div class="col-12">
                <div class="i-card border">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:40px;height:40px;border-radius:50%;background:#6c63ff;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex-shrink:0;">1</div>
                        <h5 class="mb-0">{{ translate('Add an SMM API Provider') }}</h5>
                    </div>
                    <p>{{ translate('Go to SMM Boost → Providers and click "Add Provider". Enter the provider\'s API URL and API key.') }}</p>
                    <p class="mb-2"><strong>{{ translate('Supported providers (standard SMM REST API):') }}</strong></p>
                    <ul class="mb-3">
                        <li><a href="https://justanotherpanel.com" target="_blank" rel="noopener">JustAnotherPanel</a> — <code>https://justanotherpanel.com/api/v2</code></li>
                        <li><a href="https://smmworld.net" target="_blank" rel="noopener">SMMWorld</a> — <code>https://smmworld.net/api/v2</code></li>
                        <li><a href="https://safesmm.com" target="_blank" rel="noopener">SafeSMM</a> — <code>https://safesmm.com/api/v2</code></li>
                        <li><a href="https://peaksmm.com" target="_blank" rel="noopener">PeakSMM</a> — <code>https://peaksmm.com/api/v2</code></li>
                        <li><a href="https://www.exobooster.site" target="_blank" rel="noopener">ExoBooster</a> — <code>https://www.exobooster.site/api/v2</code></li>
                        <li><a href="https://exosupplier.com/api" target="_blank" rel="noopener">ExoSupplier</a> — <code>https://exosupplier.com/api</code></li>
                    </ul>
                    <p class="text-muted fs-14">{{ translate('After saving, click "Test Connection" on the provider list to verify your API credentials.') }}</p>
                    <a href="{{ route('admin.smm.providers.create') }}" class="i-btn btn--sm btn--primary">
                        <i class="bi bi-plus-circle me-1"></i>{{ translate('Add Provider Now') }}
                    </a>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="col-12">
                <div class="i-card border">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:40px;height:40px;border-radius:50%;background:#6c63ff;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex-shrink:0;">2</div>
                        <h5 class="mb-0">{{ translate('Import Services from Provider') }}</h5>
                    </div>
                    <p>{{ translate('On the Providers list, click "Import Services" next to a provider. The system fetches all available services from the provider\'s API and saves them to your database (disabled by default).') }}</p>
                    <div class="alert" style="background:#fff3cd;border-left:4px solid #ffc107;padding:12px;border-radius:6px;">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ translate('Imported services are disabled and priced at $1.00/1000 by default. You MUST review and set correct prices before enabling them.') }}
                    </div>
                    <a href="{{ route('admin.smm.providers') }}" class="i-btn btn--sm btn--outline mt-2">
                        <i class="bi bi-download me-1"></i>{{ translate('Go to Providers') }}
                    </a>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="col-12">
                <div class="i-card border">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:40px;height:40px;border-radius:50%;background:#6c63ff;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex-shrink:0;">3</div>
                        <h5 class="mb-0">{{ translate('Set Prices, Platform & Limits') }}</h5>
                    </div>
                    <p>{{ translate('Go to SMM Boost → Services. For each service:') }}</p>
                    <ul>
                        <li>{{ translate('Set the correct Platform (Instagram, TikTok, YouTube, etc.)') }}</li>
                        <li>{{ translate('Set Service Type (followers, likes, views, subscribers, etc.)') }}</li>
                        <li>{{ translate('Set your Price per 1000 (add your markup over provider cost)') }}</li>
                        <li>{{ translate('Set Min and Max quantity allowed') }}</li>
                        <li>{{ translate('Add a Delivery Estimate (e.g. "1-3 days")') }}</li>
                    </ul>
                    <p class="text-muted fs-14">
                        {{ translate('Example: Provider charges $0.50/1000 followers → you set $1.50/1000 to earn $1.00 margin per 1000.') }}
                    </p>
                    <a href="{{ route('admin.smm.services') }}" class="i-btn btn--sm btn--outline">
                        <i class="bi bi-pencil me-1"></i>{{ translate('Review Services') }}
                    </a>
                </div>
            </div>

            {{-- Step 4 --}}
            <div class="col-12">
                <div class="i-card border">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:40px;height:40px;border-radius:50%;background:#6c63ff;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex-shrink:0;">4</div>
                        <h5 class="mb-0">{{ translate('Enable Services') }}</h5>
                    </div>
                    <p>{{ translate('Once pricing is set, toggle the status of each service to Active. Users will only see active services.') }}</p>
                    <p>{{ translate('You can enable/disable individual services at any time without losing order history.') }}</p>
                    <a href="{{ route('admin.smm.services') }}" class="i-btn btn--sm btn--success">
                        <i class="bi bi-toggle-on me-1"></i>{{ translate('Enable Services') }}
                    </a>
                </div>
            </div>

            {{-- Step 5 --}}
            <div class="col-12">
                <div class="i-card border">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width:40px;height:40px;border-radius:50%;background:#6c63ff;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;flex-shrink:0;">5</div>
                        <h5 class="mb-0">{{ translate('Monitor Orders & Troubleshoot') }}</h5>
                    </div>
                    <p>{{ translate('Go to SMM Boost → Orders to:') }}</p>
                    <ul>
                        <li>{{ translate('View all user orders with status, charge, and provider info') }}</li>
                        <li>{{ translate('Manually update order status (for edge cases)') }}</li>
                        <li>{{ translate('Refund failed or pending orders back to user wallet') }}</li>
                        <li>{{ translate('Sync order status manually with provider') }}</li>
                        <li>{{ translate('View full API request/response logs per order') }}</li>
                    </ul>
                    <p class="text-muted fs-14">
                        <i class="bi bi-clock me-1"></i>
                        {{ translate('Order statuses sync automatically every 10 minutes via the scheduler. Make sure your cron job is running:') }}<br>
                        <code>* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1</code>
                    </p>
                    <a href="{{ route('admin.smm.orders') }}" class="i-btn btn--sm btn--outline">
                        <i class="bi bi-list-check me-1"></i>{{ translate('View Orders') }}
                    </a>
                </div>
            </div>

            {{-- Order Status Reference --}}
            <div class="col-12">
                <div class="i-card border">
                    <h5 class="mb-3">{{ translate('Order Status Reference') }}</h5>
                    <div class="row g-3">
                        @foreach(['pending' => ['warning','Pending','Order accepted, not yet sent to provider'], 'processing' => ['primary','Processing','Sent to provider, awaiting fulfillment'], 'completed' => ['success','Completed','Delivered successfully'], 'failed' => ['danger','Failed','Provider returned an error'], 'refunded' => ['secondary','Refunded','Amount returned to user wallet']] as $status => $info)
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-{{ $info[0] }} text-white">{{ $info[1] }}</span>
                                <small class="text-muted">{{ translate($info[2]) }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
