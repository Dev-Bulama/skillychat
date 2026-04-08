@extends('layouts.master')
@section('content')
<div class="row g-4">
    {{-- Lead header --}}
    <div class="col-12">
        <div class="i-card">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold fs-24"
                        style="width:56px;height:56px;background:#f3f0ff;color:#6d28d9;flex-shrink:0;">
                        {{ strtoupper(substr($lead->name, 0, 1)) }}
                    </span>
                    <div>
                        <h4 class="fw-bold mb-1">{{ $lead->name }}</h4>
                        <div class="d-flex flex-wrap gap-2 fs-13 text-muted">
                            @if($lead->company)<span><i class="bi bi-building me-1"></i>{{ $lead->company }}</span>@endif
                            @if($lead->email)<span><i class="bi bi-envelope me-1"></i>{{ $lead->email }}</span>@endif
                            @if($lead->phone)<span><i class="bi bi-phone me-1"></i>{{ $lead->phone }}</span>@endif
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge" style="background:{{ $lead->stage->color }};color:#fff;">{{ $lead->stage->name }}</span>
                            <span class="badge bg-{{ $lead->priority === 'high' ? 'danger' : ($lead->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
                                {{ ucfirst($lead->priority) }} Priority
                            </span>
                            <span class="badge bg-light text-dark border">{{ ucfirst($lead->source) }}</span>
                            @foreach($lead->tagsArray() as $tag)
                            <span class="badge" style="background:#f3f0ff;color:#6d28d9;">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('user.crm.edit', $lead->uid) }}" class="i-btn btn--sm btn--outline"><i class="bi bi-pencil me-1"></i>{{ translate('Edit') }}</a>
                    <a href="{{ route('user.crm.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="col-md-3 col-6">
        <div class="i-card border text-center">
            <div class="fw-bold fs-24 mb-1" style="color:#6d28d9;">${{ number_format($lead->deal_value, 0) }}</div>
            <div class="fs-13 text-muted">{{ translate('Deal Value') }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="i-card border text-center">
            <div class="fw-bold fs-24 mb-1">{{ $lead->activities->count() }}</div>
            <div class="fs-13 text-muted">{{ translate('Activities') }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="i-card border text-center">
            <div class="fw-bold fs-24 mb-1">{{ $lead->enrollments->count() }}</div>
            <div class="fs-13 text-muted">{{ translate('Drip Sequences') }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="i-card border text-center">
            <div class="fw-bold fs-24 mb-1">{{ $lead->messages->count() }}</div>
            <div class="fs-13 text-muted">{{ translate('WA Messages') }}</div>
        </div>
    </div>

    {{-- Move stage --}}
    <div class="col-md-6">
        <div class="i-card">
            <h6 class="fw-semibold mb-3"><i class="bi bi-arrow-right-circle me-2" style="color:#6d28d9;"></i>{{ translate('Move to Stage') }}</h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach($pipelines as $pipeline)
                @foreach($pipeline->stages as $stage)
                <form action="{{ route('user.crm.update-stage') }}" method="post" class="d-inline">
                    @csrf
                    <input type="hidden" name="uid" value="{{ $lead->uid }}">
                    <input type="hidden" name="stage_id" value="{{ $stage->id }}">
                    <button type="submit" class="i-btn btn--sm {{ $lead->stage_id == $stage->id ? 'btn--primary' : 'btn--outline' }}"
                        style="{{ $lead->stage_id == $stage->id ? '' : 'border-color:'.$stage->color.';color:'.$stage->color.';' }}">
                        {{ $stage->name }}
                    </button>
                </form>
                @endforeach
                @endforeach
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($lead->notes)
    <div class="col-md-6">
        <div class="i-card">
            <h6 class="fw-semibold mb-2"><i class="bi bi-sticky me-2"></i>{{ translate('Notes') }}</h6>
            <p class="fs-14 text-muted mb-0">{{ $lead->notes }}</p>
        </div>
    </div>
    @endif

    {{-- Activity log + Add note --}}
    <div class="col-md-8">
        <div class="i-card-md">
            <div class="card--header">
                <h5 class="card-title">{{ translate('Activity Log') }}</h5>
            </div>
            <div class="card-body">
                {{-- Add note form --}}
                <div class="mb-4">
                    <div class="input-group">
                        <input type="text" id="noteInput" class="form-control" placeholder="{{ translate('Add a note, call log, or update…') }}" maxlength="1000">
                        <button class="btn btn-primary" id="addNoteBtn">{{ translate('Add') }}</button>
                    </div>
                </div>
                <div id="activityList">
                    @forelse($lead->activities as $activity)
                    <div class="d-flex gap-3 mb-3">
                        <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                            style="width:32px;height:32px;background:{{ match($activity->type){'stage_change'=>'#f0fdf4','email'=>'#ecfeff','sms'=>'#f0fdf4','whatsapp'=>'#f0fdf4',default=>'#f3f0ff'} }};font-size:14px;">
                            <i class="bi {{ match($activity->type){'stage_change'=>'bi-arrow-right-circle','email'=>'bi-envelope','sms'=>'bi-phone','whatsapp'=>'bi-whatsapp',default=>'bi-chat-text'} }}" style="color:{{ match($activity->type){'stage_change'=>'#16a34a','email'=>'#0891b2','sms'=>'#16a34a','whatsapp'=>'#16a34a',default=>'#6d28d9'} }};"></i>
                        </span>
                        <div class="flex-grow-1">
                            @if($activity->title)<p class="fw-semibold mb-0 fs-14">{{ $activity->title }}</p>@endif
                            <p class="fs-13 text-muted mb-0">{{ $activity->description }}</p>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted fs-14 text-center py-3">{{ translate('No activities yet') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Drip enrollments --}}
    <div class="col-md-4">
        <div class="i-card-md">
            <div class="card--header"><h5 class="card-title">{{ translate('Drip Sequences') }}</h5></div>
            <div class="card-body">
                @forelse($lead->enrollments as $enr)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-funnel text-muted"></i>
                    <div class="flex-grow-1">
                        <p class="mb-0 fs-14 fw-semibold">{{ $enr->sequence->name }}</p>
                        <small class="text-muted">{{ ucfirst($enr->status) }} · Step {{ $enr->current_step }}</small>
                    </div>
                    <span class="badge {{ $enr->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $enr->status }}</span>
                </div>
                @empty
                <p class="text-muted fs-13 text-center py-3">{{ translate('Not enrolled in any sequence') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
@push('script-push')
<script nonce="{{ csp_nonce() }}">
document.getElementById('addNoteBtn')?.addEventListener('click', function() {
    const note = document.getElementById('noteInput').value.trim();
    if(!note) return;
    fetch('{{ route('user.crm.note', $lead->uid) }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({note})
    }).then(r=>r.json()).then(d=>{
        if(d.success) {
            const div = document.createElement('div');
            div.className = 'd-flex gap-3 mb-3';
            div.innerHTML = `<span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:32px;height:32px;background:#f3f0ff;font-size:14px;"><i class="bi bi-chat-text" style="color:#6d28d9;"></i></span><div class="flex-grow-1"><p class="fs-13 text-muted mb-0">${note}</p><small class="text-muted">just now</small></div>`;
            const list = document.getElementById('activityList');
            list.querySelector('p.text-center')?.remove();
            list.prepend(div);
            document.getElementById('noteInput').value = '';
        }
    });
});
</script>
@endpush
