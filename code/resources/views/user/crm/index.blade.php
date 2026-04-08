@extends('layouts.master')
@push('style-include')
<style nonce="{{ csp_nonce() }}">
.kanban-board { display:flex; gap:16px; overflow-x:auto; padding-bottom:16px; min-height:520px; }
.kanban-col   { flex:0 0 280px; background:#f8f9fa; border-radius:12px; display:flex; flex-direction:column; }
.kanban-col-header { padding:12px 14px; border-radius:12px 12px 0 0; font-weight:600; font-size:14px; display:flex; align-items:center; justify-content:space-between; }
.kanban-cards { flex:1; padding:10px; overflow-y:auto; max-height:520px; }
.kanban-card  { background:#fff; border-radius:8px; padding:12px; margin-bottom:10px; cursor:grab; border:1px solid #e5e7eb; transition:box-shadow .15s; }
.kanban-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.1); }
.kanban-card.dragging { opacity:.5; box-shadow:0 8px 20px rgba(0,0,0,.15); }
.kanban-col.drag-over { outline:2px dashed #6d28d9; outline-offset:2px; }
.priority-dot { width:8px;height:8px;border-radius:50%;display:inline-block; }
</style>
@endpush
@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-kanban me-2" style="color:#6d28d9;"></i>{{ translate('Lead CRM') }}</h4>
                <p class="text-muted mb-0 fs-14">{{ translate('Drag and drop leads across stages.') }}</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <div class="input-group input-group-sm" style="width:200px;">
                    <select class="form-select form-select-sm" id="pipelineSelect" onchange="window.location='{{ route('user.crm.index') }}?pipeline='+this.value">
                        @foreach($pipelines as $p)
                        <option value="{{ $p->id }}" {{ $pipeline?->id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('user.crm.leads') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-list-ul me-1"></i>{{ translate('List View') }}</a>
                <a href="{{ route('user.crm.create') }}" class="i-btn btn--sm btn--primary"><i class="bi bi-plus-circle me-1"></i>{{ translate('Add Lead') }}</a>
                <a href="{{ route('user.crm.pipeline') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-gear me-1"></i>{{ translate('Pipelines') }}</a>
            </div>
        </div>

        @if(!$pipeline)
        <div class="i-card text-center py-5">
            <i class="bi bi-kanban fs-50 text-muted d-block mb-3"></i>
            <h5>{{ translate('No Pipeline Yet') }}</h5>
            <p class="text-muted mb-4 fs-14">{{ translate('Set up your first pipeline to start tracking leads visually.') }}</p>
            <a href="{{ route('user.crm.pipeline') }}" class="i-btn btn--md btn--primary capsuled me-2">
                <i class="bi bi-plus-circle me-1"></i>{{ translate('Create Pipeline') }}
            </a>
        </div>
        @else
        <div class="kanban-board" id="kanbanBoard">
            @foreach($pipeline->stages as $stage)
            <div class="kanban-col" data-stage-id="{{ $stage->id }}">
                <div class="kanban-col-header" style="background:{{ $stage->color }}20;border-left:4px solid {{ $stage->color }};">
                    <span>
                        <span class="priority-dot me-2" style="background:{{ $stage->color }};"></span>
                        {{ $stage->name }}
                    </span>
                    <span class="badge" style="background:{{ $stage->color }};color:#fff;">{{ $stage->leads->count() }}</span>
                </div>
                <div class="kanban-cards" id="col-{{ $stage->id }}">
                    @foreach($stage->leads as $lead)
                    <div class="kanban-card" draggable="true" data-uid="{{ $lead->uid }}" data-lead-id="{{ $lead->id }}">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <a href="{{ route('user.crm.show', $lead->uid) }}" class="fw-semibold fs-14 text-dark text-decoration-none">{{ $lead->name }}</a>
                            <span class="priority-dot flex-shrink-0 mt-1" style="background:{{ match($lead->priority){ 'high'=>'#ef4444','medium'=>'#f59e0b',default=>'#10b981'} }};"></span>
                        </div>
                        @if($lead->company)
                        <p class="fs-12 text-muted mb-1"><i class="bi bi-building me-1"></i>{{ $lead->company }}</p>
                        @endif
                        @if($lead->email)
                        <p class="fs-12 text-muted mb-1 text-truncate"><i class="bi bi-envelope me-1"></i>{{ $lead->email }}</p>
                        @endif
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            @if($lead->deal_value > 0)
                            <span class="badge bg-success fs-11">${{ number_format($lead->deal_value, 0) }}</span>
                            @else
                            <span></span>
                            @endif
                            <span class="badge bg-light text-dark fs-11">{{ ucfirst($lead->source) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
@push('script-push')
<script nonce="{{ csp_nonce() }}">
// Kanban drag-and-drop
let dragging = null;
document.querySelectorAll('.kanban-card').forEach(card => {
    card.addEventListener('dragstart', () => { dragging = card; card.classList.add('dragging'); });
    card.addEventListener('dragend',  () => { dragging = null; card.classList.remove('dragging'); });
});
document.querySelectorAll('.kanban-col').forEach(col => {
    col.addEventListener('dragover',  e => { e.preventDefault(); col.classList.add('drag-over'); });
    col.addEventListener('dragleave', () => col.classList.remove('drag-over'));
    col.addEventListener('drop', e => {
        e.preventDefault();
        col.classList.remove('drag-over');
        if(!dragging) return;
        col.querySelector('.kanban-cards').appendChild(dragging);
        const stageId = col.dataset.stageId;
        const uid     = dragging.dataset.uid;
        fetch('{{ route('user.crm.update-stage') }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({uid, stage_id: stageId})
        }).then(r=>r.json()).then(d => {
            if(!d.success) alert('Failed to update stage');
            // Update column counts
            document.querySelectorAll('.kanban-col').forEach(c => {
                const cnt = c.querySelector('.kanban-cards').querySelectorAll('.kanban-card').length;
                c.querySelector('.badge').textContent = cnt;
            });
        });
    });
});
</script>
@endpush
