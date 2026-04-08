@extends('layouts.master')
@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="i-card-md">
            <div class="card--header">
                <div>
                    <h4 class="card-title"><i class="bi bi-funnel me-2" style="color:#dc2626;"></i>{{ translate('Drip Sequences') }}</h4>
                    <p class="text-muted fs-13 mb-0">{{ translate('Automated multi-step messaging via Email, SMS, and WhatsApp.') }}</p>
                </div>
                <a href="{{ route('user.drip.create') }}" class="i-btn btn--sm btn--primary">
                    <i class="bi bi-plus-circle me-1"></i>{{ translate('New Sequence') }}
                </a>
            </div>
            <div class="card-body">
                @if($sequences->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-funnel fs-50 text-muted d-block mb-3"></i>
                    <h5>{{ translate('No Sequences Yet') }}</h5>
                    <p class="text-muted mb-4 fs-14" style="max-width:420px;margin:auto;">
                        {{ translate('Create an automated sequence that sends emails, SMS, and WhatsApp messages to your leads over time.') }}
                    </p>
                    <a href="{{ route('user.drip.create') }}" class="i-btn btn--md btn--primary capsuled">
                        <i class="bi bi-plus-circle me-1"></i>{{ translate('Create First Sequence') }}
                    </a>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle fs-14">
                        <thead>
                            <tr>
                                <th>{{ translate('Name') }}</th>
                                <th>{{ translate('Trigger') }}</th>
                                <th>{{ translate('Steps') }}</th>
                                <th>{{ translate('Enrolled') }}</th>
                                <th>{{ translate('Completed') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sequences as $seq)
                            <tr>
                                <td>
                                    <a href="{{ route('user.drip.show', $seq->uid) }}" class="fw-semibold text-decoration-none">{{ $seq->name }}</a>
                                    @if($seq->description)<br><small class="text-muted">{{ Str::limit($seq->description,50) }}</small>@endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ match($seq->trigger_type){'manual'=>'Manual','lead_created'=>'New Lead','tag_added'=>'Tag Added','chatbot_end'=>'Chatbot End','whatsapp_optin'=>'WA Opt-in',default=>'Manual'} }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $seq->steps_count ?? $seq->steps->count() }}</td>
                                <td class="text-center">{{ $seq->enrollments_count ?? $seq->total_enrolled }}</td>
                                <td class="text-center">{{ $seq->total_completed }}</td>
                                <td>
                                    <span class="badge {{ match($seq->status){'active'=>'bg-success','draft'=>'bg-warning text-dark','paused'=>'bg-secondary',default=>'bg-secondary'} }}">
                                        {{ ucfirst($seq->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a href="{{ route('user.drip.show', $seq->uid) }}" class="i-btn btn--sm btn--primary"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('user.drip.edit', $seq->uid) }}" class="i-btn btn--sm btn--outline"><i class="bi bi-pencil"></i></a>
                                        @if($seq->status !== 'active')
                                        <form action="{{ route('user.drip.activate', $seq->uid) }}" method="post" class="d-inline">
                                            @csrf
                                            <button type="submit" class="i-btn btn--sm btn--success" title="{{ translate('Activate') }}"><i class="bi bi-play-fill"></i></button>
                                        </form>
                                        @else
                                        <form action="{{ route('user.drip.pause', $seq->uid) }}" method="post" class="d-inline">
                                            @csrf
                                            <button type="submit" class="i-btn btn--sm btn--outline" title="{{ translate('Pause') }}"><i class="bi bi-pause-fill"></i></button>
                                        </form>
                                        @endif
                                        <form action="{{ route('user.drip.destroy', $seq->uid) }}" method="post" onsubmit="return confirm('Delete this sequence?')">
                                            @csrf @method('DELETE')
                                            <button class="i-btn btn--sm btn--danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $sequences->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
