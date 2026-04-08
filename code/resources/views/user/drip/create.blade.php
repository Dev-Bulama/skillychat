@extends('layouts.master')
@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-xl-8">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-funnel me-2" style="color:#dc2626;"></i>{{ $sequence ? translate('Edit Sequence') : translate('New Drip Sequence') }}</h4>
                <a href="{{ route('user.drip.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}</a>
            </div>
            <div class="card-body">

                @if(!$sequence)
                <div class="alert mb-4" style="background:#fff5f5;border-left:4px solid #dc2626;padding:12px 16px;border-radius:8px;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span class="fs-14"><i class="bi bi-magic me-2" style="color:#dc2626;"></i><strong>{{ translate('Load Demo Sequence') }}</strong> — {{ translate('See a sample Welcome Sequence to understand the structure.') }}</span>
                        <button type="button" id="demoBtn" class="i-btn btn--sm capsuled" style="background:#dc2626;color:#fff;border:none;">
                            <i class="bi bi-play-circle me-1"></i>{{ translate('Load Demo') }}
                        </button>
                    </div>
                </div>
                @endif

                <form action="{{ $sequence ? route('user.drip.update', $sequence->uid) : route('user.drip.store') }}" method="post">
                    @csrf
                    @if($sequence) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">{{ translate('Sequence Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="seq_name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $sequence->name ?? '') }}" placeholder="Welcome Sequence">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ translate('Trigger') }} <span class="text-danger">*</span></label>
                            <select name="trigger_type" id="seq_trigger" class="form-select @error('trigger_type') is-invalid @enderror">
                                @foreach(['manual'=>'Manual (Enroll manually)','lead_created'=>'New Lead Created','tag_added'=>'Tag Added to Lead','chatbot_end'=>'Chatbot Session Ends','whatsapp_optin'=>'WhatsApp Opt-in'] as $val=>$label)
                                <option value="{{ $val }}" {{ old('trigger_type', $sequence->trigger_type ?? 'manual') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('trigger_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12" id="triggerValueRow" style="{{ in_array(old('trigger_type', $sequence->trigger_type ?? 'manual'), ['tag_added']) ? '' : 'display:none;' }}">
                            <label class="form-label fw-semibold">{{ translate('Trigger Value') }}</label>
                            <input type="text" name="trigger_value" class="form-control"
                                value="{{ old('trigger_value', $sequence->trigger_value ?? '') }}"
                                placeholder="{{ translate('e.g. tag name for tag_added trigger') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ translate('Description') }}</label>
                            <textarea name="description" id="seq_desc" class="form-control" rows="2"
                                placeholder="{{ translate('Optional description of this sequence…') }}">{{ old('description', $sequence->description ?? '') }}</textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="i-btn btn--md btn--primary capsuled">
                                <i class="bi bi-save me-1"></i>{{ $sequence ? translate('Save Changes') : translate('Create Sequence') }}
                            </button>
                            <a href="{{ route('user.drip.index') }}" class="i-btn btn--md btn--outline capsuled">{{ translate('Cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Info card --}}
    <div class="col-xl-4">
        <div class="i-card" style="border:2px dashed #fca5a5;">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2" style="color:#dc2626;"></i>{{ translate('How Triggers Work') }}</h6>
            <div class="fs-13 text-muted">
                <p><strong style="color:#111;">Manual</strong> — You enroll contacts one by one or via import from the sequence detail page.</p>
                <p><strong style="color:#111;">New Lead Created</strong> — Auto-enrolls every new CRM lead the moment it is created.</p>
                <p><strong style="color:#111;">Tag Added</strong> — Enrolls a lead when a specific tag (trigger value) is added to it.</p>
                <p><strong style="color:#111;">Chatbot Session Ends</strong> — Enrolls the user after a chatbot conversation ends.</p>
                <p class="mb-0"><strong style="color:#111;">WhatsApp Opt-in</strong> — Enrolls new WhatsApp contacts when they first message you.</p>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script-push')
<script nonce="{{ csp_nonce() }}">
// Show trigger value field for tag_added
document.getElementById('seq_trigger')?.addEventListener('change', function() {
    document.getElementById('triggerValueRow').style.display = this.value === 'tag_added' ? '' : 'none';
});

// Demo data
document.getElementById('demoBtn')?.addEventListener('click', function() {
    fetch('{{ route('user.drip.demo-data') }}')
        .then(r => r.json())
        .then(d => {
            if (!d.status) return;
            const s = d.sequence;
            document.getElementById('seq_name').value    = s.name;
            document.getElementById('seq_desc').value    = s.description;
            document.getElementById('seq_trigger').value = s.trigger_type;
            document.getElementById('seq_trigger').dispatchEvent(new Event('change'));
            this.innerHTML = '✅ Demo Loaded — Edit with your real data';
            this.style.background = '#16a34a';
        });
});
</script>
@endpush
