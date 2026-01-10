@extends('admin.layouts.master')

@section('content')
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">{{ translate('Conversation Details') }}</h4>
                    <a href="{{ route('admin.chatbot.conversations') }}" class="i-btn btn--sm danger">
                        <i class="las la-arrow-left"></i> {{ translate('Back to Conversations') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Chatbot') }}</label>
                                <p>
                                    <a href="{{ route('admin.chatbot.show', $conversation->chatbot->uid) }}" class="text-primary">
                                        {{ $conversation->chatbot->name }}
                                    </a>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Owner') }}</label>
                                <p>
                                    @if($conversation->chatbot->user)
                                        <a href="{{ route('admin.user.show', $conversation->chatbot->user->id) }}" class="text-info">
                                            {{ $conversation->chatbot->user->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Visitor ID') }}</label>
                                <p class="font-monospace">{{ $conversation->visitor_id }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Status') }}</label>
                                <p>
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
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Assigned Agent') }}</label>
                                <p>
                                    @if($conversation->assignedAgent && $conversation->assignedAgent->user)
                                        {{ $conversation->assignedAgent->user->name }}
                                        <br>
                                        <small class="text-muted">
                                            {{ translate('Taken over') }}: {{ diff_for_humans($conversation->taken_over_at) }}
                                        </small>
                                    @else
                                        <span class="text-muted">{{ translate('No agent assigned') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ translate('Started') }}</label>
                                <p>
                                    {{ get_date_time($conversation->created_at) }}
                                    <br>
                                    <small class="text-muted">{{ diff_for_humans($conversation->created_at) }}</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">
                        {{ translate('Conversation Messages') }}
                        <span class="badge badge--primary">{{ $conversation->messages->count() }} {{ translate('messages') }}</span>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="chat-messages" style="max-height: 600px; overflow-y: auto;">
                        @forelse($conversation->messages as $message)
                            <div class="message-item mb-3 {{ $message->sender_type == 'visitor' ? 'text-start' : 'text-end' }}">
                                <div class="d-inline-block {{ $message->sender_type == 'visitor' ? 'bg-light' : 'bg-primary text-white' }} p-3 rounded" style="max-width: 70%;">
                                    <div class="message-sender mb-1">
                                        <strong>
                                            @if($message->sender_type == 'visitor')
                                                <i class="las la-user"></i> {{ translate('Visitor') }}
                                            @elseif($message->sender_type == 'ai')
                                                <i class="las la-robot"></i> {{ translate('AI Bot') }}
                                            @elseif($message->sender_type == 'agent')
                                                <i class="las la-user-tie"></i> {{ $message->agent->user->name ?? translate('Agent') }}
                                            @endif
                                        </strong>
                                    </div>
                                    <div class="message-content">
                                        {{ $message->message }}
                                    </div>
                                    @if($message->confidence_score)
                                        <div class="message-meta mt-2">
                                            <small>
                                                <i class="las la-chart-line"></i> 
                                                {{ translate('Confidence') }}: {{ number_format($message->confidence_score * 100, 1) }}%
                                            </small>
                                        </div>
                                    @endif
                                    <div class="message-time mt-1">
                                        <small class="{{ $message->sender_type == 'visitor' ? 'text-muted' : 'text-white-50' }}">
                                            {{ get_date_time($message->created_at) }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="las la-comments la-3x"></i>
                                <p>{{ translate('No messages in this conversation yet') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style-push')
<style nonce="{{ csp_nonce() }}">
    .chat-messages {
        padding: 1rem;
    }
    .message-item {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush
