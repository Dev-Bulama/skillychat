@extends('layouts.master')
@section('content')

<div class="row g-3">
    <div class="col-12">
        <div class="i-card-md">
            <div class="card-header">
                <h4 class="card-title">{{$campaign->name}}</h4>
                <div class="d-flex gap-2">
                    @if($campaign->isDraft())
                    <a href="{{route('user.bulk-sms.launch', $campaign->uid)}}" class="i-btn success btn--md"
                       onclick="return confirm('{{translate('Launch this campaign now?')}}')">
                        <i class="bi bi-send me-1"></i> {{translate('Launch')}}
                    </a>
                    @endif
                    <a href="{{route('user.bulk-sms.index')}}" class="i-btn danger btn--md">
                        <i class="bi bi-arrow-left me-1"></i> {{translate('Back')}}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 border rounded text-center">
                            <div class="fs-4 fw-bold text-primary">{{$campaign->total_recipients}}</div>
                            <small class="text-muted">{{translate('Recipients')}}</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 border rounded text-center">
                            <div class="fs-4 fw-bold text-success">{{$campaign->sent_count}}</div>
                            <small class="text-muted">{{translate('Sent')}}</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 border rounded text-center">
                            <div class="fs-4 fw-bold text-danger">{{$campaign->failed_count}}</div>
                            <small class="text-muted">{{translate('Failed')}}</small>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="p-3 border rounded text-center">
                            @php
                                $colors = ['draft'=>'secondary','queued'=>'info','processing'=>'warning','completed'=>'success','failed'=>'danger'];
                                $color  = $colors[$campaign->status] ?? 'secondary';
                            @endphp
                            <span class="badge badge--{{$color}} fs-6">{{ucfirst($campaign->status)}}</span>
                            <small class="d-block text-muted mt-1">{{translate('Status')}}</small>
                        </div>
                    </div>
                </div>

                <dl class="row">
                    <dt class="col-sm-3">{{translate('Provider')}}</dt>
                    <dd class="col-sm-9">{{$campaign->provider?->name ?? '—'}}</dd>
                    <dt class="col-sm-3">{{translate('Sender ID')}}</dt>
                    <dd class="col-sm-9">{{$campaign->sender_id ?? '—'}}</dd>
                    <dt class="col-sm-3">{{translate('Message')}}</dt>
                    <dd class="col-sm-9"><pre class="mb-0" style="white-space:pre-wrap">{{$campaign->message}}</pre></dd>
                    <dt class="col-sm-3">{{translate('Groups')}}</dt>
                    <dd class="col-sm-9">{{$campaign->groups->pluck('name')->join(', ')}}</dd>
                    @if($campaign->failure_reason)
                    <dt class="col-sm-3 text-danger">{{translate('Error')}}</dt>
                    <dd class="col-sm-9 text-danger">{{$campaign->failure_reason}}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    @if($campaign->logs->isNotEmpty())
    <div class="col-12">
        <div class="i-card-md">
            <div class="card--header"><h5 class="card-title">{{translate('Message Logs')}} ({{translate('last 100')}})</h5></div>
            <div class="card-body px-0">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>{{translate('Phone')}}</th>
                                <th>{{translate('Status')}}</th>
                                <th>{{translate('Sent At')}}</th>
                                <th>{{translate('Error')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaign->logs as $log)
                            <tr>
                                <td>{{$log->phone}}</td>
                                <td><span class="badge badge--{{$log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : 'secondary')}}">{{ucfirst($log->status)}}</span></td>
                                <td>{{$log->sent_at?->diffForHumans() ?? '—'}}</td>
                                <td class="text-danger">{{$log->error ?? '—'}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
