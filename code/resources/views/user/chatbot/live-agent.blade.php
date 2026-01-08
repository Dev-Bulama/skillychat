@extends('layouts.master')
@section('content')

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Pending')}}</p>
                        <h3 class="mb-0">{{$stats['pending']}}</h3>
                    </div>
                    <div class="icon-box warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Active')}}</p>
                        <h3 class="mb-0">{{$stats['active']}}</h3>
                    </div>
                    <div class="icon-box success">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Resolved Today')}}</p>
                        <h3 class="mb-0">{{$stats['resolved_today']}}</h3>
                    </div>
                    <div class="icon-box info">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="i-card-md">
    <div class="card-header">
        <h4 class="card-title">
            {{translate(Arr::get($meta_data,'title'))}}
        </h4>
        <div class="d-flex gap-2">
            @if($agent)
                <select class="form-select" id="agent-status" style="width: 150px;">
                    <option value="online" {{$agent->status === 'online' ? 'selected' : ''}}>{{translate('Online')}}</option>
                    <option value="away" {{$agent->status === 'away' ? 'selected' : ''}}>{{translate('Away')}}</option>
                    <option value="busy" {{$agent->status === 'busy' ? 'selected' : ''}}>{{translate('Busy')}}</option>
                    <option value="offline" {{$agent->status === 'offline' ? 'selected' : ''}}>{{translate('Offline')}}</option>
                </select>
            @endif
        </div>
    </div>

    <div class="card-body px-0">
        @if($conversations->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{translate('Visitor')}}</th>
                            <th scope="col">{{translate('Chatbot')}}</th>
                            <th scope="col">{{translate('Status')}}</th>
                            <th scope="col">{{translate('Messages')}}</th>
                            <th scope="col">{{translate('Last Message')}}</th>
                            <th scope="col">{{translate('Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($conversations as $conversation)
                            <tr>
                                <td data-label="#">{{$loop->iteration}}</td>
                                <td data-label='{{translate("Visitor")}}'>
                                    <strong>{{$conversation->visitor_name ?? 'Anonymous'}}</strong>
                                    @if($conversation->visitor_email)
                                        <br><small class="text-muted">{{$conversation->visitor_email}}</small>
                                    @endif
                                </td>
                                <td data-label='{{translate("Chatbot")}}'>{{$conversation->chatbot->name}}</td>
                                <td data-label='{{translate("Status")}}'>
                                    @if($conversation->status === 'human_requested')
                                        <span class="badge badge--warning">{{translate('Pending')}}</span>
                                    @elseif($conversation->status === 'human_active')
                                        <span class="badge badge--success">{{translate('Active')}}</span>
                                    @else
                                        <span class="badge badge--info">{{translate('Resolved')}}</span>
                                    @endif
                                </td>
                                <td data-label='{{translate("Messages")}}'>{{$conversation->messages_count}}</td>
                                <td data-label='{{translate("Last Message")}}'>{{$conversation->last_message_at?->diffForHumans()}}</td>
                                <td data-label='{{translate("Action")}}'>
                                    <button class="i-btn primary btn--sm view-conversation" data-uid="{{$conversation->uid}}">
                                        <i class="bi bi-eye me-1"></i> {{translate('View')}}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="m-3">
                {{$conversations->links()}}
            </div>
        @else
            @include('admin.partials.not_found')
        @endif
    </div>
</div>

<div class="modal fade" id="conversationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{translate('Conversation')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="conversation-container" style="height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px;">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <form id="message-form">
                    <div class="input-group">
                        <textarea class="form-control" id="message-input" rows="2" placeholder="{{translate('Type your message...')}}"></textarea>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="resolve-btn">{{translate('Resolve')}}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{translate('Close')}}</button>
            </div>
        </div>
    </div>
</div>

@push('script-push')
<script nonce="{{ csp_nonce() }}">
    'use strict';

    let currentConversationUid = null;

    document.querySelectorAll('.view-conversation').forEach(btn => {
        btn.addEventListener('click', function() {
            currentConversationUid = this.dataset.uid;
            loadConversation(currentConversationUid);
            new bootstrap.Modal(document.getElementById('conversationModal')).show();
        });
    });

    document.getElementById('message-form').addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });

    document.getElementById('resolve-btn').addEventListener('click', function() {
        resolveConversation();
    });

    function loadConversation(uid) {
        fetch(`{{route('user.live-agent.conversation', '')}}/${uid}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessages(data.messages);
                }
            });
    }

    function displayMessages(messages) {
        const container = document.getElementById('conversation-container');
        container.innerHTML = '';

        messages.forEach(msg => {
            const div = document.createElement('div');
            div.className = `message mb-3 ${msg.sender_type === 'visitor' ? 'text-end' : ''}`;
            div.innerHTML = `
                <div class="d-inline-block" style="max-width: 70%;">
                    <div class="p-2 rounded ${msg.sender_type === 'visitor' ? 'bg-primary text-white' : 'bg-light'}">
                        ${msg.message}
                    </div>
                    <small class="text-muted">${msg.formatted_time}</small>
                </div>
            `;
            container.appendChild(div);
        });

        container.scrollTop = container.scrollHeight;
    }

    function sendMessage() {
        const message = document.getElementById('message-input').value;
        if (!message.trim()) return;

        fetch('{{route('user.live-agent.send-message')}}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            },
            body: JSON.stringify({
                conversation_id: currentConversationUid,
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('message-input').value = '';
                loadConversation(currentConversationUid);
            }
        });
    }

    function resolveConversation() {
        fetch('{{route('user.live-agent.resolve')}}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            },
            body: JSON.stringify({
                conversation_id: currentConversationUid
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
</script>
@endpush

@endsection
