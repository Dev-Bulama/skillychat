@extends('layouts.master')
@section('content')

<div class="i-card-md">
    <div class="card-header">
        <h4 class="card-title"><i class="bi bi-phone me-1"></i> {{translate('New SMS Campaign')}}</h4>
        <a href="{{route('user.bulk-sms.index')}}" class="i-btn danger btn--md">
            <i class="bi bi-arrow-left me-1"></i> {{translate('Back')}}
        </a>
    </div>
    <div class="card-body">
        @if($providers->isEmpty())
        <div class="alert alert-warning">
            {{translate('You have not configured an SMS provider yet.')}}
            <a href="{{route('user.bulk-sms.provider')}}">{{translate('Add one now')}}</a>
        </div>
        @endif

        <form action="{{route('user.bulk-sms.store')}}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{translate('Campaign Name')}} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{old('name')}}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{translate('SMS Provider')}} <span class="text-danger">*</span></label>
                    <select class="form-select" name="sms_provider_id" required>
                        <option value="">{{translate('Select provider...')}}</option>
                        @foreach($providers as $provider)
                        <option value="{{$provider->id}}" {{old('sms_provider_id') == $provider->id ? 'selected':''}}>
                            {{$provider->name}} ({{$provider->provider}})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{translate('Sender ID / From')}}
                        <small class="text-muted">{{translate('(optional — override provider default)')}}</small>
                    </label>
                    <input type="text" class="form-control" name="sender_id" maxlength="15" value="{{old('sender_id')}}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{translate('Schedule For')}}
                        <small class="text-muted">{{translate('(leave blank to launch immediately)')}}</small>
                    </label>
                    <input type="datetime-local" class="form-control" name="scheduled_at" value="{{old('scheduled_at')}}">
                </div>

                <div class="col-12">
                    <label class="form-label">{{translate('Contact Groups')}} <span class="text-danger">*</span></label>
                    @if($groups->isEmpty())
                        <p class="text-muted">{{translate('No contact groups found.')}}
                            <a href="{{route('user.bulk-sms.contacts')}}">{{translate('Create a group')}}</a>
                        </p>
                    @else
                    <div class="row g-2">
                        @foreach($groups as $group)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check border rounded p-2">
                                <input class="form-check-input" type="checkbox" name="group_ids[]"
                                    value="{{$group->id}}" id="grp_{{$group->id}}"
                                    {{in_array($group->id, (array) old('group_ids', [])) ? 'checked' : ''}}>
                                <label class="form-check-label" for="grp_{{$group->id}}">
                                    <strong>{{$group->name}}</strong>
                                    <small class="d-block text-muted">{{$group->contact_count}} {{translate('contacts')}}</small>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="col-12">
                    <label class="form-label">{{translate('Message')}} <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="message" rows="4" maxlength="1600" required
                        placeholder="{{translate('Hello {name}, your message here...')}}">{{old('message')}}</textarea>
                    <small class="text-muted">
                        {{translate('Available placeholders:')}} <code>{name}</code>, <code>{phone}</code>, <code>{email}</code>
                    </small>
                </div>

                <div class="col-12">
                    <button type="submit" class="i-btn primary btn--md">
                        <i class="bi bi-save me-1"></i> {{translate('Save as Draft')}}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
