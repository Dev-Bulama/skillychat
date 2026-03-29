@extends('layouts.master')
@section('content')

<div class="row g-3">
    <div class="col-12">
        <div class="i-card-md">
            <div class="card-header">
                <h4 class="card-title">{{$campaign->name}}</h4>
                <div class="d-flex gap-2">
                    @if($campaign->isDraft())
                    <a href="{{route('user.bulk-email.launch', $campaign->uid)}}" class="i-btn success btn--md"
                       onclick="return confirm('{{translate('Launch this campaign now?')}}')">
                        <i class="bi bi-send me-1"></i> {{translate('Launch')}}
                    </a>
                    @endif
                    <a href="{{route('user.bulk-email.index')}}" class="i-btn danger btn--md">
                        <i class="bi bi-arrow-left me-1"></i> {{translate('Back')}}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    @foreach([
                        ['value' => $campaign->total_recipients, 'label' => 'Recipients', 'color' => 'primary'],
                        ['value' => $campaign->sent_count,       'label' => 'Sent',       'color' => 'success'],
                        ['value' => $campaign->failed_count,     'label' => 'Failed',     'color' => 'danger'],
                    ] as $stat)
                    <div class="col-sm-4">
                        <div class="p-3 border rounded text-center">
                            <div class="fs-4 fw-bold text-{{$stat['color']}}">{{$stat['value']}}</div>
                            <small class="text-muted">{{translate($stat['label'])}}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                <dl class="row">
                    <dt class="col-sm-3">{{translate('Subject')}}</dt>
                    <dd class="col-sm-9">{{$campaign->subject}}</dd>
                    <dt class="col-sm-3">{{translate('From')}}</dt>
                    <dd class="col-sm-9">{{$campaign->from_name}} &lt;{{$campaign->from_email}}&gt;</dd>
                    <dt class="col-sm-3">{{translate('Groups')}}</dt>
                    <dd class="col-sm-9">{{$campaign->groups->pluck('name')->join(', ')}}</dd>
                    <dt class="col-sm-3">{{translate('Status')}}</dt>
                    <dd class="col-sm-9"><span class="badge badge--{{['draft'=>'secondary','queued'=>'info','processing'=>'warning','completed'=>'success','failed'=>'danger'][$campaign->status] ?? 'secondary'}}">{{ucfirst($campaign->status)}}</span></dd>
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
            <div class="card--header"><h5 class="card-title">{{translate('Email Logs')}} ({{translate('last 100')}})</h5></div>
            <div class="card-body px-0">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>{{translate('Email')}}</th>
                                <th>{{translate('Status')}}</th>
                                <th>{{translate('Sent At')}}</th>
                                <th>{{translate('Error')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaign->logs as $log)
                            <tr>
                                <td>{{$log->email}}</td>
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
