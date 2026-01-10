@extends('admin.layouts.master')

@section('content')
    <div class="row g-3 mb-3 row-cols-xxl-6 row-cols-md-3 row-cols-sm-2 row-cols-1">
        @php
            $cards = ([
                [
                    "class"  => 'col',
                    "title"  => translate("Total Chatbots"),
                    "total"  => $statistics['total_chatbots'],
                    "icon"   => '<i class="las la-robot"></i>',
                    "bg"     => 'primary',
                ],
                [
                    "class"  => 'col',
                    "title"  => translate("Active Chatbots"),
                    "total"  => $statistics['active_chatbots'],
                    "icon"   => '<i class="las la-check-circle"></i>',
                    "bg"     => 'success',
                ],
                [
                    "class"  => 'col',
                    "title"  => translate("Total Conversations"),
                    "total"  => $statistics['total_conversations'],
                    "icon"   => '<i class="las la-comments"></i>',
                    "bg"     => 'info',
                ],
                [
                    "class"  => 'col',
                    "title"  => translate("Active Conversations"),
                    "total"  => $statistics['active_conversations'],
                    "icon"   => '<i class="las la-comment-dots"></i>',
                    "bg"     => 'warning',
                ],
                [
                    "class"  => 'col',
                    "title"  => translate("Total Messages"),
                    "total"  => $statistics['total_messages'],
                    "icon"   => '<i class="las la-envelope"></i>',
                    "bg"     => 'danger',
                ],
                [
                    "class"  => 'col',
                    "title"  => translate("Users with Chatbots"),
                    "total"  => $statistics['total_users_with_chatbots'],
                    "icon"   => '<i class="las la-users"></i>',
                    "bg"     => 'dark',
                ]
            ]);
        @endphp
        @include("admin.partials.report_card")
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-6">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">
                        {{translate("Chatbots by AI Provider")}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{translate('Provider')}}</th>
                                    <th>{{translate('Count')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($usage_by_provider as $provider)
                                    <tr>
                                        <td>
                                            <span class="badge badge--{{ $provider->ai_provider == 'openai' ? 'success' : ($provider->ai_provider == 'gemini' ? 'info' : 'primary') }}">
                                                {{ ucfirst($provider->ai_provider) }}
                                            </span>
                                        </td>
                                        <td>{{ $provider->count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">{{translate('No data available')}}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">
                        {{translate("Conversations by Status")}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{translate('Status')}}</th>
                                    <th>{{translate('Count')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($conversations_by_status as $status)
                                    <tr>
                                        <td>
                                            @php
                                                $statusBadge = match($status->status) {
                                                    'ai_active' => 'primary',
                                                    'human_requested' => 'warning',
                                                    'human_active' => 'info',
                                                    'resolved' => 'success',
                                                    'closed' => 'secondary',
                                                    default => 'dark'
                                                };
                                            @endphp
                                            <span class="badge badge--{{ $statusBadge }}">
                                                {{ ucwords(str_replace('_', ' ', $status->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $status->count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">{{translate('No data available')}}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-6">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">
                        {{translate("Recent Chatbots")}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{translate('Name')}}</th>
                                    <th>{{translate('User')}}</th>
                                    <th>{{translate('Provider')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    <th>{{translate('Created')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_chatbots as $chatbot)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.chatbot.show', $chatbot->uid) }}" class="text-primary">
                                                {{ $chatbot->name }}
                                            </a>
                                        </td>
                                        <td>{{ $chatbot->user->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge--{{ $chatbot->ai_provider == 'openai' ? 'success' : ($chatbot->ai_provider == 'gemini' ? 'info' : 'primary') }}">
                                                {{ ucfirst($chatbot->ai_provider) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge--{{ $chatbot->status == 1 ? 'success' : 'danger' }}">
                                                {{ $chatbot->status == 1 ? translate('Active') : translate('Inactive') }}
                                            </span>
                                        </td>
                                        <td>{{ diff_for_humans($chatbot->created_at) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{translate('No chatbots yet')}}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">
                        {{translate("Recent Conversations")}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{translate('Chatbot')}}</th>
                                    <th>{{translate('User')}}</th>
                                    <th>{{translate('Visitor')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    <th>{{translate('Started')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_conversations as $conversation)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.chatbot.conversation.details', $conversation->uid) }}" class="text-primary">
                                                {{ $conversation->chatbot->name }}
                                            </a>
                                        </td>
                                        <td>{{ $conversation->chatbot->user->name ?? 'N/A' }}</td>
                                        <td>{{ substr($conversation->visitor_id, 0, 8) }}...</td>
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
                                        <td>{{ diff_for_humans($conversation->created_at) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{translate('No conversations yet')}}</td>
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
