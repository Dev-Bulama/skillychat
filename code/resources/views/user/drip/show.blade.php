@extends('layouts.master')
@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="i-card">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-funnel fs-20" style="color:#dc2626;"></i>
                        <h4 class="fw-bold mb-0">{{ $sequence->name }}</h4>
                        <span class="badge {{ match($sequence->status){'active'=>'bg-success','draft'=>'bg-warning text-dark','paused'=>'bg-secondary',default=>'bg-secondary'} }}">
                            {{ ucfirst($sequence->status) }}
                        </span>
                    </div>
                    @if($sequence->description)<p class="text-muted fs-14 mb-0">{{ $sequence->description }}</p>@endif
                    <div class="mt-2 fs-13 text-muted">
                        <span class="badge bg-light text-dark border me-1">
                            {{ match($sequence->trigger_type){'manual'=>'Manual','lead_created'=>'New Lead','tag_added'=>'Tag Added','chatbot_end'=>'Chatbot End','whatsapp_optin'=>'WA Opt-in',default=>'Manual'} }}
                        </span>
                        <span>{{ $steps->count() }} {{ translate('steps') }}</span>
                        <span class="mx-2">·</span>
                        <span>{{ $enrollments->total() }} {{ translate('enrolled') }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('user.drip.edit', $sequence->uid) }}" class="i-btn btn--sm btn--outline"><i class="bi bi-pencil me-1"></i>{{ translate('Edit') }}</a>
                    @if($sequence->status !== 'active')
                    <form action="{{ route('user.drip.activate', $sequence->uid) }}" method="post" class="d-inline">
                        @csrf
                        <button type="submit" class="i-btn btn--sm btn--success"><i class="bi bi-play-fill me-1"></i>{{ translate('Activate') }}</button>
                    </form>
                    @else
                    <form action="{{ route('user.drip.pause', $sequence->uid) }}" method="post" class="d-inline">
                        @csrf
                        <button type="submit" class="i-btn btn--sm btn--outline"><i class="bi bi-pause-fill me-1"></i>{{ translate('Pause') }}</button>
                    </form>
                    @endif
                    <a href="{{ route('user.drip.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-arrow-left me-1"></i>{{ translate('Back') }}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Steps --}}
    <div class="col-md-7">
        <div class="i-card-md">
            <div class="card--header">
                <h5 class="card-title">{{ translate('Sequence Steps') }}</h5>
                <span class="badge bg-light text-dark border">{{ $steps->count() }} {{ translate('steps') }}</span>
            </div>
            <div class="card-body">
                @forelse($steps->sortBy('position') as $step)
                <div class="d-flex gap-3 mb-3 p-3 rounded-3" style="background:#fafafa;border:1px solid #f0f0f0;">
                    <div class="d-flex flex-column align-items-center" style="min-width:40px;">
                        <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white fs-13"
                            style="width:32px;height:32px;background:{{ $step->channel === 'email' ? '#0891b2' : ($step->channel === 'sms' ? '#16a34a' : '#25d366') }};">
                            {{ $loop->iteration }}
                        </span>
                        @if(!$loop->last)
                        <div style="width:2px;flex-grow:1;background:#e5e7eb;margin:4px 0;"></div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge" style="background:{{ $step->channel === 'email' ? '#ecfeff' : ($step->channel === 'sms' ? '#f0fdf4' : '#dcfce7') }};color:{{ $step->channel === 'email' ? '#0891b2' : ($step->channel === 'sms' ? '#16a34a' : '#15803d') }};">
                                <i class="bi bi-{{ $step->channel === 'email' ? 'envelope' : ($step->channel === 'sms' ? 'phone' : 'whatsapp') }} me-1"></i>{{ ucfirst($step->channel) }}
                            </span>
                            <small class="text-muted">{{ $step->delayDescription() }}</small>
                        </div>
                        @if($step->subject)<p class="fw-semibold mb-1 fs-14">{{ $step->subject }}</p>@endif
                        <p class="fs-13 text-muted mb-0" style="white-space:pre-line;">{{ Str::limit($step->message, 120) }}</p>
                    </div>
                    <div>
                        <form action="{{ route('user.drip.step.destroy', [$sequence->uid, $step->id]) }}" method="post"
                            onsubmit="return confirm('{{ translate('Delete this step?') }}')">
                            @csrf @method('DELETE')
                            <button class="i-btn btn--sm btn--danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted fs-14">
                    <i class="bi bi-list-ol fs-40 d-block mb-2 text-muted"></i>
                    {{ translate('No steps yet. Add the first step below.') }}
                </div>
                @endforelse

                {{-- Add step form --}}
                <div class="mt-4 p-3 rounded-3" style="background:#fff5f5;border:1px solid #fca5a5;">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-plus-circle me-2" style="color:#dc2626;"></i>{{ translate('Add Step') }}</h6>
                    <form action="{{ route('user.drip.step.store', $sequence->uid) }}" method="post">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold fs-13">{{ translate('Channel') }}</label>
                                <select name="channel" class="form-select form-select-sm" id="stepChannel">
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold fs-13">{{ translate('Delay Days') }}</label>
                                <input type="number" name="delay_days" class="form-control form-control-sm" value="0" min="0" placeholder="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold fs-13">{{ translate('Delay Hours') }}</label>
                                <input type="number" name="delay_hours" class="form-control form-control-sm" value="0" min="0" placeholder="0">
                            </div>
                            <div class="col-12" id="subjectRow">
                                <label class="form-label fw-semibold fs-13">{{ translate('Email Subject') }}</label>
                                <input type="text" name="subject" class="form-control form-control-sm" placeholder="e.g. Welcome to SkilyChat!">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold fs-13">{{ translate('Message') }} <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control form-control-sm" rows="4"
                                    placeholder="{{ translate('Hi {{contact_name}}, …') }}" required></textarea>
                                <small class="text-muted fs-12">{{ translate('Use') }} <code>&#123;&#123;contact_name&#125;&#125;</code>, <code>&#123;&#123;contact_email&#125;&#125;</code>, <code>&#123;&#123;contact_phone&#125;&#125;</code> {{ translate('as placeholders.') }}</small>
                            </div>
                            <div class="col-md-6" id="fromNameRow">
                                <label class="form-label fw-semibold fs-13">{{ translate('From Name') }}</label>
                                <input type="text" name="from_name" class="form-control form-control-sm" placeholder="The Team">
                            </div>
                            <div class="col-md-6" id="fromEmailRow">
                                <label class="form-label fw-semibold fs-13">{{ translate('From Email') }}</label>
                                <input type="email" name="from_email" class="form-control form-control-sm" placeholder="hello@yourcompany.com">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="i-btn btn--sm btn--primary capsuled">
                                    <i class="bi bi-plus-circle me-1"></i>{{ translate('Add Step') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Enroll + Enrollments --}}
    <div class="col-md-5">

        {{-- Enroll contact --}}
        @if($sequence->status === 'active')
        <div class="i-card mb-4">
            <h6 class="fw-semibold mb-3"><i class="bi bi-person-plus me-2" style="color:#dc2626;"></i>{{ translate('Enroll a Contact') }}</h6>
            <form action="{{ route('user.drip.enroll', $sequence->uid) }}" method="post">
                @csrf
                <div class="row g-2">
                    <div class="col-12">
                        <input type="text" name="contact_name" class="form-control form-control-sm" placeholder="{{ translate('Name (optional)') }}">
                    </div>
                    <div class="col-12">
                        <input type="email" name="contact_email" class="form-control form-control-sm" placeholder="{{ translate('Email address') }}">
                    </div>
                    <div class="col-12">
                        <input type="text" name="contact_phone" class="form-control form-control-sm" placeholder="{{ translate('Phone / WhatsApp number') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="i-btn btn--sm btn--primary w-100 capsuled">
                            <i class="bi bi-person-check me-1"></i>{{ translate('Enroll Contact') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @else
        <div class="i-card mb-4 text-center py-3" style="border:2px dashed #e5e7eb;">
            <i class="bi bi-lock fs-28 text-muted d-block mb-2"></i>
            <p class="text-muted fs-13 mb-0">{{ translate('Activate the sequence to enroll contacts.') }}</p>
        </div>
        @endif

        {{-- Enrollments list --}}
        <div class="i-card-md">
            <div class="card--header">
                <h5 class="card-title">{{ translate('Enrolled Contacts') }}</h5>
                <span class="badge bg-light text-dark border">{{ $enrollments->total() }}</span>
            </div>
            <div class="card-body">
                @forelse($enrollments as $enr)
                <div class="d-flex align-items-start gap-2 mb-3 pb-3 border-bottom">
                    <span class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white fs-12 flex-shrink-0"
                        style="width:30px;height:30px;background:#6d28d9;">
                        {{ strtoupper(substr($enr->contact_name ?: $enr->contact_email ?: '?', 0, 1)) }}
                    </span>
                    <div class="flex-grow-1">
                        <p class="fw-semibold mb-0 fs-14">{{ $enr->contact_name ?: $enr->contact_email ?: $enr->contact_phone }}</p>
                        <small class="text-muted">Step {{ $enr->current_step }} · {{ ucfirst($enr->status) }}</small>
                        @if($enr->next_send_at && $enr->status === 'active')
                        <br><small class="text-muted">Next: {{ $enr->next_send_at->format('d M Y H:i') }}</small>
                        @endif
                    </div>
                    <div class="d-flex flex-column gap-1 align-items-end">
                        <span class="badge {{ $enr->status === 'active' ? 'bg-success' : ($enr->status === 'completed' ? 'bg-primary' : 'bg-secondary') }}">
                            {{ ucfirst($enr->status) }}
                        </span>
                        @if($enr->status === 'active')
                        <form action="{{ route('user.drip.unenroll', $enr->id) }}" method="post">
                            @csrf
                            <button class="i-btn btn--sm btn--danger" title="{{ translate('Unenroll') }}"><i class="bi bi-person-dash"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted fs-13 text-center py-3">{{ translate('No one enrolled yet.') }}</p>
                @endforelse
                {{ $enrollments->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
@push('script-push')
<script nonce="{{ csp_nonce() }}">
// Toggle email-specific fields
document.getElementById('stepChannel')?.addEventListener('change', function() {
    const isEmail = this.value === 'email';
    ['subjectRow','fromNameRow','fromEmailRow'].forEach(id => {
        document.getElementById(id).style.display = isEmail ? '' : 'none';
    });
});
</script>
@endpush
