@extends('layouts.master')
@section('content')

<div class="i-card-md">
    <div class="card-header">
        <h4 class="card-title">
            <i class="bi bi-phone me-1"></i> {{translate(Arr::get($meta_data,'title'))}}
        </h4>
        <a href="{{route('user.bulk-sms.create')}}" class="i-btn primary btn--md capsuled">
            <i class="bi bi-plus-circle me-1"></i> {{translate('New Campaign')}}
        </a>
    </div>

    <div class="card-body px-0">
        @if($campaigns->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{translate('Name')}}</th>
                        <th>{{translate('Provider')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th>{{translate('Recipients')}}</th>
                        <th>{{translate('Sent')}}</th>
                        <th>{{translate('Failed')}}</th>
                        <th>{{translate('Created')}}</th>
                        <th>{{translate('Action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td><strong>{{$campaign->name}}</strong></td>
                        <td>{{$campaign->provider?->name ?? '—'}}</td>
                        <td>
                            @php
                                $statusColors = ['draft'=>'secondary','queued'=>'info','processing'=>'warning','completed'=>'success','failed'=>'danger','paused'=>'dark'];
                                $color = $statusColors[$campaign->status] ?? 'secondary';
                            @endphp
                            <span class="badge badge--{{$color}}">{{ucfirst($campaign->status)}}</span>
                        </td>
                        <td>{{$campaign->total_recipients}}</td>
                        <td>{{$campaign->sent_count}}</td>
                        <td>{{$campaign->failed_count}}</td>
                        <td>{{$campaign->created_at->diffForHumans()}}</td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{route('user.bulk-sms.show', $campaign->uid)}}" class="i-btn info btn--sm">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($campaign->isDraft())
                                <a href="{{route('user.bulk-sms.launch', $campaign->uid)}}" class="i-btn success btn--sm"
                                   onclick="return confirm('{{translate('Launch this campaign?')}}')">
                                    <i class="bi bi-send"></i>
                                </a>
                                <a href="{{route('user.bulk-sms.destroy', $campaign->uid)}}" class="i-btn danger btn--sm"
                                   onclick="return confirm('{{translate('Delete this campaign?')}}')">
                                    <i class="bi bi-trash"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-3 pt-2">
            {{$campaigns->links()}}
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-phone fs-1 d-block mb-2"></i>
            <p>{{translate('No campaigns yet.')}} <a href="{{route('user.bulk-sms.create')}}">{{translate('Create one')}}</a></p>
        </div>
        @endif
    </div>
</div>

@endsection
