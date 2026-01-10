@extends('admin.layouts.master')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Chatbot Details') }}</h4>
                    <a href="{{ route('admin.chatbot.list') }}" class="i-btn btn--sm danger">
                        <i class="las la-arrow-left"></i> {{ translate('Back to List') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Chatbot Name') }}</label>
                                <p>{{ $chatbot->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('UID') }}</label>
                                <p class="font-monospace">{{ $chatbot->uid }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Owner') }}</label>
                                <p>
                                    @if($chatbot->user)
                                        <a href="{{ route('admin.user.show', $chatbot->user->id) }}" class="text-info">
                                            {{ $chatbot->user->name }}
                                        </a>
                                        <br><small class="text-muted">{{ $chatbot->user->email }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('AI Provider') }}</label>
                                <p>
                                    @php
                                        $providerColor = match($chatbot->ai_provider) {
                                            'openai' => 'success',
                                            'gemini' => 'info',
                                            'claude' => 'primary',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge--{{ $providerColor }}">
                                        {{ ucfirst($chatbot->ai_provider) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Status') }}</label>
                                <p>
                                    <span class="badge badge--{{ $chatbot->status == 1 ? 'success' : 'danger' }}">
                                        {{ $chatbot->status == 1 ? translate('Active') : translate('Inactive') }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Widget Type') }}</label>
                                <p>
                                    <span class="badge badge--dark">{{ ucfirst($chatbot->widget_type) }}</span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Human Takeover') }}</label>
                                <p>
                                    <span class="badge badge--{{ $chatbot->human_takeover_enabled ? 'success' : 'secondary' }}">
                                        {{ $chatbot->human_takeover_enabled ? translate('Enabled') : translate('Disabled') }}
                                    </span>
                                </p>
                            </div>
                            @if($chatbot->human_takeover_enabled)
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('AI Confidence Threshold') }}</label>
                                <p>{{ $chatbot->ai_confidence_threshold * 100 }}%</p>
                            </div>
                            @endif
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Welcome Message') }}</label>
                                <p>{{ $chatbot->welcome_message ?: translate('Not set') }}</p>
                            </div>
                        </div>

                        @if($chatbot->domain)
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Allowed Domain') }}</label>
                                <p class="font-monospace">{{ $chatbot->domain }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Embed Code') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control font-monospace" id="embedCode" value="{{ $chatbot->getEmbedCode() }}" readonly>
                                    <button class="btn btn-primary" type="button" onclick="copyEmbedCode()">
                                        <i class="las la-copy"></i> {{ translate('Copy') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Created At') }}</label>
                                <p>{{ get_date_time($chatbot->created_at) }} ({{ diff_for_humans($chatbot->created_at) }})</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 row-cols-xxl-5 row-cols-md-3 row-cols-sm-2 row-cols-1">
        @php
            $cards = ([
                [
                    "class"  => 'col',
                    "title"  => translate("Total Conversations"),
                    "total"  => $statistics['total_conversations'],
                    "icon"   => '<i class="las la-comments"></i>',
                    "bg"     => 'primary',
                ],
                [
                    "class"  => 'col',
                    "title"  => translate("Active Conversations"),
                    "total"  => $statistics['active_conversations'],
                    "icon"   => '<i class="las la-comment-dots"></i>',
                    "bg"     => 'info',
                ],
                [
                    "class"  => 'col',
                    "title"  => translate("Total Messages"),
                    "total"  => $statistics['total_messages'],
                    "icon"   => '<i class="las la-envelope"></i>',
                    "bg"     => 'success',
                ],
                [
                    "class"  => 'col',
                    "title"  => translate("Training Data"),
                    "total"  => $statistics['total_training_data'],
                    "icon"   => '<i class="las la-database"></i>',
                    "bg"     => 'warning',
                ],
                [
                    "class"  => 'col',
                    "title"  => translate("Agents"),
                    "total"  => $statistics['total_agents'],
                    "icon"   => '<i class="las la-user-tie"></i>',
                    "bg"     => 'dark',
                ]
            ]);
        @endphp
        @include("admin.partials.report_card")
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Recent Conversations') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{translate('Visitor ID')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    <th>{{translate('Assigned Agent')}}</th>
                                    <th>{{translate('Started')}}</th>
                                    <th>{{translate('Last Activity')}}</th>
                                    <th>{{translate('Actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_conversations as $conversation)
                                    <tr>
                                        <td>
                                            <span class="font-monospace">{{ substr($conversation->visitor_id, 0, 16) }}...</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusBadge = match($conversation->status) {
                                                    'ai_active' => 'primary',
                                                    'human_requested' => 'warning',
                                                    'human_active' => 'info',
                                                    'resolved' => 'success',
                                                    'closed' => 'secondary',
                                                    default => 'dark'
                                                };
                                            @endphp
                                            <span class="badge badge--{{ $statusBadge }}">
                                                {{ ucwords(str_replace('_', ' ', $conversation->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($conversation->assignedAgent)
                                                {{ $conversation->assignedAgent->user->name ?? 'N/A' }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ diff_for_humans($conversation->created_at) }}</td>
                                        <td>{{ diff_for_humans($conversation->updated_at) }}</td>
                                        <td>
                                            <a href="{{ route('admin.chatbot.conversation.details', $conversation->uid) }}" class="i-btn primary--btn btn--sm">
                                                <i class="las la-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ translate('No conversations yet') }}</td>
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

@push('script-push')
<script nonce="{{ csp_nonce() }}">
    function copyEmbedCode() {
        const embedCode = document.getElementById('embedCode');
        embedCode.select();
        embedCode.setSelectionRange(0, 99999);
        document.execCommand('copy');
        alert('{{ translate("Embed code copied to clipboard!") }}');
    }
</script>
@endpush
