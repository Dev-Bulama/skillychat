@extends('admin.layouts.master')
@section('content')

<div class="row justify-content-center">
    <div class="col-xl-10">

        @if(session('status'))
            <div class="alert alert-{{ session('type','success') === 'error' ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ── Configuration Card ─────────────────────────────────────────── --}}
        <div class="i-card-md mb-4">
            <div class="card--header">
                <div>
                    <h4 class="card-title">
                        <i class="bi bi-building me-2" style="color:#4285f4;"></i>
                        {{ translate('Google Business Profile — Configuration') }}
                    </h4>
                    <p class="text-muted mb-0 fs-13">
                        {{ translate('Enter your Google OAuth 2.0 credentials to enable the Google Business Profile integration for your users.') }}
                    </p>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.setting.google.business.save') }}" method="post" id="google-settings-form">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Google OAuth Client ID') }}</label>
                            <input type="text" name="google_oauth_client_id" class="form-control"
                                value="{{ site_settings('google_oauth_client_id') }}"
                                placeholder="123456789-xxxxxxxx.apps.googleusercontent.com">
                            <small class="text-muted fs-12">{{ translate('From Google Cloud Console → APIs & Services → Credentials') }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Google OAuth Client Secret') }}</label>
                            <div class="input-group">
                                <input type="password" name="google_oauth_client_secret" class="form-control" id="clientSecretInput"
                                    value="{{ site_settings('google_oauth_client_secret') }}"
                                    placeholder="GOCSPX-xxxxxxxxxxxxxxxx">
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="var f=document.getElementById('clientSecretInput');f.type=f.type==='password'?'text':'password';">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted fs-12">{{ translate('Keep this secret — never share it publicly.') }}</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Authorized Redirect URI') }}</label>
                            <div class="input-group">
                                <input type="url" name="google_oauth_redirect_uri" class="form-control"
                                    value="{{ site_settings('google_oauth_redirect_uri', url('/user/google-business/callback')) }}"
                                    placeholder="{{ url('/user/google-business/callback') }}">
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="navigator.clipboard.writeText(document.querySelector('[name=google_oauth_redirect_uri]').value)">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <small class="text-muted fs-12">
                                {{ translate('Copy this URL and add it to the Authorized Redirect URIs in your Google Cloud Console OAuth credentials.') }}
                                <br>
                                {{ translate('Default:') }} <code>{{ url('/user/google-business/callback') }}</code>
                            </small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold d-block">{{ translate('Feature Status') }}</label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="enabled" value="0">
                                <input class="form-check-input" type="checkbox" name="enabled" id="featureToggle" value="1"
                                    {{ \App\Models\FeatureFlag::enabled('google_business_profile') ? 'checked' : '' }}>
                                <label class="form-check-label" for="featureToggle">
                                    {{ translate('Enable Google Business Profile for all users') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-12 pt-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-save me-1"></i>{{ translate('Save Settings') }}
                            </button>
                        </div>
                    </div>
                </form>

                <hr class="my-4">

                {{-- ── Setup Guide ─────────────────────────────────────────── --}}
                <h5 class="fw-semibold mb-3"><i class="bi bi-journal-text me-2"></i>{{ translate('Google Cloud Console Setup Guide') }}</h5>

                <div class="accordion" id="setupGuide">

                    {{-- Step 1 --}}
                    <div class="accordion-item border mb-2 rounded-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold rounded-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#step1">
                                <span class="badge bg-primary me-2">1</span>
                                {{ translate('Create a Google Cloud Project') }}
                            </button>
                        </h2>
                        <div id="step1" class="accordion-collapse collapse" data-bs-parent="#setupGuide">
                            <div class="accordion-body fs-14 text-muted">
                                <ol class="ps-3">
                                    <li>{{ translate('Go to') }} <a href="https://console.cloud.google.com" target="_blank">console.cloud.google.com</a></li>
                                    <li>{{ translate('Click the project dropdown at the top → "New Project"') }}</li>
                                    <li>{{ translate('Enter a project name (e.g. "My Business Integration") → click "Create"') }}</li>
                                    <li>{{ translate('Wait for the project to be created, then select it from the dropdown') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="accordion-item border mb-2 rounded-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold rounded-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#step2">
                                <span class="badge bg-primary me-2">2</span>
                                {{ translate('Enable Required APIs') }}
                            </button>
                        </h2>
                        <div id="step2" class="accordion-collapse collapse" data-bs-parent="#setupGuide">
                            <div class="accordion-body fs-14 text-muted">
                                <p>{{ translate('In your project, navigate to') }} <strong>APIs & Services → Library</strong>. {{ translate('Search for and enable each of these:') }}</p>
                                <ul class="ps-3">
                                    <li><strong>My Business Account Management API</strong></li>
                                    <li><strong>Business Profile Performance API</strong></li>
                                    <li><strong>My Business Business Information API</strong></li>
                                    <li><strong>My Business Notifications API</strong></li>
                                </ul>
                                <p>{{ translate('For each API: click its name → click the blue "Enable" button → wait for activation.') }}</p>
                                <div class="alert alert-warning py-2 fs-13">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    {{ translate('Note: These APIs require your application to be approved by Google. During development, use test user accounts added in the OAuth consent screen.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="accordion-item border mb-2 rounded-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold rounded-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#step3">
                                <span class="badge bg-primary me-2">3</span>
                                {{ translate('Create OAuth 2.0 Credentials') }}
                            </button>
                        </h2>
                        <div id="step3" class="accordion-collapse collapse" data-bs-parent="#setupGuide">
                            <div class="accordion-body fs-14 text-muted">
                                <ol class="ps-3">
                                    <li>{{ translate('Go to') }} <strong>APIs & Services → Credentials</strong></li>
                                    <li>{{ translate('Click "+ Create Credentials" → "OAuth client ID"') }}</li>
                                    <li>{{ translate('Application type: select "Web application"') }}</li>
                                    <li>{{ translate('Name: enter anything, e.g. "Business Profile Web Client"') }}</li>
                                    <li>{{ translate('Under "Authorized redirect URIs" → click "+ Add URI"') }}</li>
                                    <li>{{ translate('Paste this exact URL:') }}
                                        <div class="input-group mt-1 mb-1" style="max-width:500px;">
                                            <input type="text" class="form-control form-control-sm font-monospace bg-light"
                                                value="{{ url('/user/google-business/callback') }}" readonly id="callbackUrlGuide">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="navigator.clipboard.writeText(document.getElementById('callbackUrlGuide').value)">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </li>
                                    <li>{{ translate('Click "Create" — Google will show you the Client ID and Client Secret') }}</li>
                                    <li>{{ translate('Copy both values into the form above') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="accordion-item border mb-2 rounded-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold rounded-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#step4">
                                <span class="badge bg-primary me-2">4</span>
                                {{ translate('Configure OAuth Consent Screen') }}
                            </button>
                        </h2>
                        <div id="step4" class="accordion-collapse collapse" data-bs-parent="#setupGuide">
                            <div class="accordion-body fs-14 text-muted">
                                <ol class="ps-3">
                                    <li>{{ translate('Go to') }} <strong>APIs & Services → OAuth consent screen</strong></li>
                                    <li>{{ translate('User type: select "External" → click "Create"') }}</li>
                                    <li>{{ translate('Fill in the required fields:') }}
                                        <ul class="mt-1">
                                            <li><strong>{{ translate('App name') }}</strong>: {{ translate('your platform name') }}</li>
                                            <li><strong>{{ translate('User support email') }}</strong>: {{ translate('your admin email') }}</li>
                                            <li><strong>{{ translate('Developer contact information') }}</strong>: {{ translate('your email') }}</li>
                                        </ul>
                                    </li>
                                    <li>{{ translate('Click "Save and Continue" → on the Scopes page, click "Add or Remove Scopes"') }}</li>
                                    <li>{{ translate('Manually enter this scope:') }}
                                        <code class="d-block mt-1 mb-1 p-1 bg-light rounded">https://www.googleapis.com/auth/business.manage</code>
                                    </li>
                                    <li>{{ translate('Click "Update" → "Save and Continue"') }}</li>
                                    <li>{{ translate('Under "Test users" → click "+ Add users" → add the Google accounts you want to test with') }}</li>
                                    <li>{{ translate('Click "Save and Continue" → "Back to Dashboard"') }}</li>
                                </ol>
                                <div class="alert alert-info py-2 fs-13 mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    {{ translate('While in "Testing" mode, only test users you add can connect. To allow all users, submit your app for Google verification under the "Publishing status" section.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 5 --}}
                    <div class="accordion-item border rounded-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold rounded-3" type="button"
                                data-bs-toggle="collapse" data-bs-target="#step5">
                                <span class="badge bg-success me-2">5</span>
                                {{ translate('Save Credentials & Go Live') }}
                            </button>
                        </h2>
                        <div id="step5" class="accordion-collapse collapse" data-bs-parent="#setupGuide">
                            <div class="accordion-body fs-14 text-muted">
                                <ol class="ps-3">
                                    <li>{{ translate('Copy the "Client ID" from Google Cloud → paste into the "Google OAuth Client ID" field above') }}</li>
                                    <li>{{ translate('Copy the "Client Secret" → paste into the "Google OAuth Client Secret" field above') }}</li>
                                    <li>{{ translate('Confirm the Redirect URI matches exactly:') }} <code>{{ url('/user/google-business/callback') }}</code></li>
                                    <li>{{ translate('Toggle "Enable Google Business Profile" to ON') }}</li>
                                    <li>{{ translate('Click "Save Settings"') }}</li>
                                    <li>{{ translate('Test by connecting a Google account from the user panel') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                </div>{{-- end accordion --}}
            </div>
        </div>

        {{-- ── Connected Users Card ────────────────────────────────────────── --}}
        <div class="i-card-md">
            <div class="card--header">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-people me-2"></i>{{ translate('Connected Users') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ translate('User') }}</th>
                                <th>{{ translate('Google Email') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Locations') }}</th>
                                <th>{{ translate('Connected') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(isset($connectedUsers) ? $connectedUsers : (isset($accounts) ? $accounts : collect()) as $ga)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $ga->user->name ?? '—' }}</div>
                                    <small class="text-muted">{{ $ga->user->email ?? '—' }}</small>
                                </td>
                                <td>{{ $ga->google_email ?? '—' }}</td>
                                <td>
                                    @if($ga->status === 'connected')
                                        <span class="badge bg-success">{{ translate('Connected') }}</span>
                                    @elseif($ga->status === 'expired')
                                        <span class="badge bg-warning text-dark">{{ translate('Expired') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ translate('Disconnected') }}</span>
                                    @endif
                                </td>
                                <td>{{ $ga->locations_count ?? 0 }}</td>
                                <td>{{ $ga->created_at ? $ga->created_at->format('M j, Y') : '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    {{ translate('No users have connected their Google accounts yet.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
