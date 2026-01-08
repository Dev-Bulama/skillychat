@extends('layouts.master')
@section('content')

<div class="i-card-md mb-3">
    <div class="card-header">
        <h4 class="card-title">
            {{translate(Arr::get($meta_data,'title'))}}
        </h4>
        <div class="d-flex gap-2">
            <select class="form-select" onchange="window.location.href='?date_range=' + this.value" style="width: 200px;">
                <option value="today" {{$dateRange === 'today' ? 'selected' : ''}}>{{translate('Today')}}</option>
                <option value="yesterday" {{$dateRange === 'yesterday' ? 'selected' : ''}}>{{translate('Yesterday')}}</option>
                <option value="last_7_days" {{$dateRange === 'last_7_days' ? 'selected' : ''}}>{{translate('Last 7 Days')}}</option>
                <option value="last_30_days" {{$dateRange === 'last_30_days' ? 'selected' : ''}}>{{translate('Last 30 Days')}}</option>
                <option value="this_month" {{$dateRange === 'this_month' ? 'selected' : ''}}>{{translate('This Month')}}</option>
            </select>
            <a href="{{route('user.chatbot.index')}}" class="i-btn danger btn--md">
                <i class="bi bi-arrow-left me-1"></i> {{translate('Back')}}
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Total Conversations')}}</p>
                        <h3 class="mb-0">{{$totalConversations}}</h3>
                    </div>
                    <div class="icon-box primary">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Total Messages')}}</p>
                        <h3 class="mb-0">{{$totalMessages}}</h3>
                    </div>
                    <div class="icon-box success">
                        <i class="bi bi-envelope"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Human Takeovers')}}</p>
                        <h3 class="mb-0">{{$humanTakeoverCount}}</h3>
                    </div>
                    <div class="icon-box warning">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="i-card-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 text-muted">{{translate('Avg Satisfaction')}}</p>
                        <h3 class="mb-0">{{$avgSatisfaction}} / 5</h3>
                    </div>
                    <div class="icon-box info">
                        <i class="bi bi-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-12">
        <div class="i-card-md">
            <div class="card-header">
                <h5 class="card-title">{{translate('Usage Statistics')}}</h5>
            </div>
            <div class="card-body">
                @if($usageLogs->count() > 0)
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{translate('Date')}}</th>
                                    <th>{{translate('Messages')}}</th>
                                    <th>{{translate('Tokens Used')}}</th>
                                    <th>{{translate('Cost')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usageLogs as $log)
                                    <tr>
                                        <td>{{$log->date}}</td>
                                        <td>{{$log->message_count}}</td>
                                        <td>{{number_format($log->total_tokens)}}</td>
                                        <td>${{number_format($log->total_cost, 4)}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">{{translate('No usage data available for this period')}}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="i-card-md">
            <div class="card-header">
                <h5 class="card-title">{{translate('Recent Conversations')}}</h5>
            </div>
            <div class="card-body">
                @if($recentConversations->count() > 0)
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{translate('Visitor')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    <th>{{translate('Messages')}}</th>
                                    <th>{{translate('Agent')}}</th>
                                    <th>{{translate('Last Message')}}</th>
                                    <th>{{translate('Rating')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentConversations as $conv)
                                    <tr>
                                        <td>{{$conv->visitor_name ?? 'Anonymous'}}</td>
                                        <td>
                                            @if($conv->status === 'ai_active')
                                                <span class="badge badge--primary">{{translate('AI Active')}}</span>
                                            @elseif($conv->status === 'human_active')
                                                <span class="badge badge--success">{{translate('Human Active')}}</span>
                                            @else
                                                <span class="badge badge--info">{{translate('Resolved')}}</span>
                                            @endif
                                        </td>
                                        <td>{{$conv->total_messages}}</td>
                                        <td>{{$conv->assignedAgent ? $conv->assignedAgent->name : '-'}}</td>
                                        <td>{{$conv->last_message_at?->diffForHumans()}}</td>
                                        <td>
                                            @if($conv->satisfaction_rated)
                                                {{$conv->satisfaction_score}} / 5
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center">{{translate('No conversations yet')}}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
