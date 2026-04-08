@extends('layouts.master')
@section('content')
<div class="row g-4">
    <div class="col-xl-5">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title">{{ translate('Pipelines') }}</h4>
                <a href="{{ route('user.crm.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Back to Board') }}</a>
            </div>
            <div class="card-body">
                <form action="{{ route('user.crm.pipeline.store') }}" method="post" class="mb-4">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="{{ translate('Pipeline name e.g. Sales, Marketing') }}">
                        <button type="submit" class="btn btn-primary">{{ translate('Create') }}</button>
                    </div>
                    @error('name')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
                </form>
                @forelse($pipelines as $pipeline)
                <div class="i-card border mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-semibold mb-0">
                            {{ $pipeline->name }}
                            @if($pipeline->is_default)<span class="badge bg-primary ms-1 fs-11">Default</span>@endif
                        </h6>
                        <form action="{{ route('user.crm.pipeline.destroy', $pipeline->uid) }}" method="post" onsubmit="return confirm('Delete this pipeline and all its leads?')">
                            @csrf @method('DELETE')
                            <button class="i-btn btn--sm btn--danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($pipeline->stages as $stage)
                        <div class="d-flex align-items-center gap-1 px-2 py-1 rounded-pill fs-12" style="background:{{ $stage->color }}20;border:1px solid {{ $stage->color }};">
                            <span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:{{ $stage->color }};"></span>
                            {{ $stage->name }}
                            <form action="{{ route('user.crm.stage.destroy', $stage->id) }}" method="post" class="d-inline ms-1">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-danger" style="font-size:10px;" title="Remove">&times;</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    <form action="{{ route('user.crm.stage.store') }}" method="post" class="row g-2">
                        @csrf
                        <input type="hidden" name="pipeline_id" value="{{ $pipeline->id }}">
                        <div class="col-6"><input type="text" name="name" class="form-control form-control-sm" placeholder="{{ translate('New stage name') }}"></div>
                        <div class="col-3"><input type="color" name="color" class="form-control form-control-sm" value="#6c757d"></div>
                        <div class="col-3"><button type="submit" class="i-btn btn--sm btn--outline w-100">{{ translate('Add') }}</button></div>
                    </form>
                </div>
                @empty
                <div class="text-center py-4 text-muted fs-14">{{ translate('No pipelines yet. Create one above.') }}</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        {{-- Demo pipeline setup guide --}}
        <div class="i-card" style="border:2px dashed #c4b5fd;">
            <h5 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2" style="color:#7c3aed;"></i>{{ translate('Getting Started with CRM') }}</h5>
            <div class="row g-3">
                @php
                $tips = [
                    ['n'=>'01','title'=>translate('Create a Pipeline'),'desc'=>translate('A pipeline represents a sales or marketing process (e.g. "Sales Funnel", "Onboarding"). Each has stages your leads move through.')],
                    ['n'=>'02','title'=>translate('Add Stages'),'desc'=>translate('Stages are the steps in your process: New → Contacted → Qualified → Proposal → Won/Lost. Color-code them for clarity.')],
                    ['n'=>'03','title'=>translate('Add Leads'),'desc'=>translate('Add leads manually, or they auto-appear from WhatsApp messages and chatbot conversations.')],
                    ['n'=>'04','title'=>translate('Drag & Drop'),'desc'=>translate('Move leads between stages by dragging them on the Kanban board. Every move is logged automatically.')],
                ];
                @endphp
                @foreach($tips as $tip)
                <div class="col-md-6">
                    <div class="p-3 rounded-3" style="background:#faf5ff;border:1px solid #e9d5ff;">
                        <span class="fw-black fs-28 lh-1" style="color:#7c3aed;opacity:.15;">{{ $tip['n'] }}</span>
                        <p class="fw-semibold mb-1 fs-14">{{ $tip['title'] }}</p>
                        <p class="fs-13 text-muted mb-0">{{ $tip['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
