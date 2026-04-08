@extends('layouts.master')
@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-9">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-person-plus me-2" style="color:#6d28d9;"></i>{{ $lead ? translate('Edit Lead') : translate('Add Lead') }}</h4>
                <a href="{{ route('user.crm.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}</a>
            </div>
            <div class="card-body">

                {{-- Demo populate --}}
                @if(!$lead)
                <div class="alert mb-4" style="background:#faf5ff;border-left:4px solid #7c3aed;padding:12px 16px;border-radius:8px;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="fs-14"><i class="bi bi-magic me-2" style="color:#7c3aed;"></i><strong>{{ translate('Load Demo Lead') }}</strong> — {{ translate('Populate with sample data to explore the form.') }}</span>
                        <button type="button" id="demoLeadBtn" class="i-btn btn--sm capsuled" style="background:#7c3aed;color:#fff;border:none;">
                            <i class="bi bi-play-circle me-1"></i>{{ translate('Load Demo Data') }}
                        </button>
                    </div>
                </div>
                @endif

                <form action="{{ $lead ? route('user.crm.update', $lead->uid) : route('user.crm.store') }}" method="post">
                    @csrf
                    @if($lead) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Full Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="l_name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $lead->name ?? '') }}" placeholder="John Adeyemi">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Email Address') }}</label>
                            <input type="email" name="email" id="l_email" class="form-control"
                                value="{{ old('email', $lead->email ?? '') }}" placeholder="john@company.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Phone Number') }}</label>
                            <input type="text" name="phone" id="l_phone" class="form-control"
                                value="{{ old('phone', $lead->phone ?? '') }}" placeholder="+2348012345678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Company') }}</label>
                            <input type="text" name="company" id="l_company" class="form-control"
                                value="{{ old('company', $lead->company ?? '') }}" placeholder="Acme Ltd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Job Title') }}</label>
                            <input type="text" name="position" class="form-control"
                                value="{{ old('position', $lead->position ?? '') }}" placeholder="Marketing Manager">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Website') }}</label>
                            <input type="url" name="website" class="form-control"
                                value="{{ old('website', $lead->website ?? '') }}" placeholder="https://company.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ translate('Pipeline') }} <span class="text-danger">*</span></label>
                            <select name="pipeline_id" id="pipelineSelect" class="form-select @error('pipeline_id') is-invalid @enderror">
                                <option value="">{{ translate('Select pipeline') }}</option>
                                @foreach($pipelines as $p)
                                <option value="{{ $p->id }}" {{ old('pipeline_id', $lead->pipeline_id ?? '') == $p->id ? 'selected' : '' }}
                                    data-stages="{{ $p->stages->map(fn($s)=>['id'=>$s->id,'name'=>$s->name])->toJson() }}">
                                    {{ $p->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('pipeline_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ translate('Stage') }} <span class="text-danger">*</span></label>
                            <select name="stage_id" id="stageSelect" class="form-select @error('stage_id') is-invalid @enderror">
                                <option value="">{{ translate('Select stage') }}</option>
                                @if($lead)
                                @foreach($lead->pipeline->stages as $s)
                                <option value="{{ $s->id }}" {{ $lead->stage_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                                @endif
                            </select>
                            @error('stage_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ translate('Deal Value') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="deal_value" class="form-control" step="0.01" min="0"
                                    value="{{ old('deal_value', $lead->deal_value ?? '') }}" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ translate('Source') }}</label>
                            <select name="source" class="form-select">
                                @foreach(['manual','chatbot','whatsapp','form','import','api'] as $src)
                                <option value="{{ $src }}" {{ old('source', $lead->source ?? 'manual') == $src ? 'selected' : '' }}>{{ ucfirst($src) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ translate('Priority') }}</label>
                            <select name="priority" class="form-select">
                                @foreach(['low','medium','high'] as $pr)
                                <option value="{{ $pr }}" {{ old('priority', $lead->priority ?? 'medium') == $pr ? 'selected' : '' }}>{{ ucfirst($pr) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ translate('Follow-up Date') }}</label>
                            <input type="datetime-local" name="follow_up_at" class="form-control"
                            value="{{ old('follow_up_at', $lead && $lead->follow_up_at ? $lead->follow_up_at->format('Y-m-d\TH:i') : '') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Tags') }}</label>
                            <input type="text" name="tags" id="l_tags" class="form-control"
                                value="{{ old('tags', $lead->tags ?? '') }}"
                                placeholder="{{ translate('hot-lead, enterprise, referred') }}">
                            <small class="text-muted">{{ translate('Comma-separated') }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ translate('Location') }}</label>
                            <input type="text" name="location" class="form-control"
                                value="{{ old('location', $lead->location ?? '') }}" placeholder="Lagos, Nigeria">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="{{ translate('Any additional notes about this lead…') }}">{{ old('notes', $lead->notes ?? '') }}</textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-save me-1"></i>{{ $lead ? translate('Save Changes') : translate('Add Lead') }}
                            </button>
                            <a href="{{ route('user.crm.index') }}" class="i-btn btn--md btn--outline capsuled">{{ translate('Cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script-push')
<script nonce="{{ csp_nonce() }}">
// Dynamic stage options based on pipeline
document.getElementById('pipelineSelect')?.addEventListener('change', function() {
    const stages = JSON.parse(this.selectedOptions[0]?.dataset.stages || '[]');
    const sel = document.getElementById('stageSelect');
    sel.innerHTML = '<option value="">Select stage</option>';
    stages.forEach(s => { sel.innerHTML += `<option value="${s.id}">${s.name}</option>`; });
});

// Demo data
document.getElementById('demoLeadBtn')?.addEventListener('click', () => {
    document.getElementById('l_name').value    = 'Emeka Okafor';
    document.getElementById('l_email').value   = 'emeka.okafor@techcorp.ng';
    document.getElementById('l_phone').value   = '+2348055123456';
    document.getElementById('l_company').value = 'TechCorp Nigeria Ltd';
    document.getElementById('l_tags').value    = 'enterprise, hot-lead, referral';
    // trigger pipeline change if available
    const pSel = document.getElementById('pipelineSelect');
    if(pSel.options.length > 1) { pSel.selectedIndex = 1; pSel.dispatchEvent(new Event('change')); }
    document.getElementById('demoLeadBtn').innerHTML = '✅ Demo Loaded — Edit with real data';
    document.getElementById('demoLeadBtn').style.background = '#16a34a';
});
</script>
@endpush
