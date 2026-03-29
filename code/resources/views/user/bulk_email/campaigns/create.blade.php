@extends('layouts.master')
@section('content')

<div class="i-card-md">
    <div class="card-header">
        <h4 class="card-title"><i class="bi bi-envelope-paper me-1"></i> {{translate('New Email Campaign')}}</h4>
        <a href="{{route('user.bulk-email.index')}}" class="i-btn danger btn--md">
            <i class="bi bi-arrow-left me-1"></i> {{translate('Back')}}
        </a>
    </div>
    <div class="card-body">
        @if($providers->isEmpty())
        <div class="alert alert-warning">
            {{translate('No email provider configured.')}}
            <a href="{{route('user.bulk-email.provider')}}">{{translate('Add one')}}</a>
        </div>
        @endif

        <form action="{{route('user.bulk-email.store')}}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">{{translate('Campaign Name')}} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{old('name')}}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{translate('Email Provider')}} <span class="text-danger">*</span></label>
                    <select class="form-select" name="email_provider_id" required>
                        <option value="">{{translate('Select provider...')}}</option>
                        @foreach($providers as $provider)
                        <option value="{{$provider->id}}" {{old('email_provider_id') == $provider->id ? 'selected':''}}>
                            {{$provider->name}} ({{$provider->provider}})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label">{{translate('Subject')}} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="subject" value="{{old('subject')}}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">{{translate('From Name')}}</label>
                    <input type="text" class="form-control" name="from_name" value="{{old('from_name')}}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{translate('From Email')}}</label>
                    <input type="email" class="form-control" name="from_email" value="{{old('from_email')}}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{translate('Reply To')}}</label>
                    <input type="email" class="form-control" name="reply_to" value="{{old('reply_to')}}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">{{translate('Content Type')}}</label>
                    <select class="form-select" name="content_type">
                        <option value="html" {{old('content_type','html') == 'html' ? 'selected':''}}>{{translate('HTML')}}</option>
                        <option value="plain" {{old('content_type') == 'plain' ? 'selected':''}}>{{translate('Plain Text')}}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{translate('Schedule For')}} <small class="text-muted">{{translate('(blank = launch manually)')}}</small></label>
                    <input type="datetime-local" class="form-control" name="scheduled_at" value="{{old('scheduled_at')}}">
                </div>

                <div class="col-12">
                    <label class="form-label">{{translate('Contact Groups')}} <span class="text-danger">*</span></label>
                    @if($groups->isEmpty())
                        <p class="text-muted">{{translate('No contact groups. ')}}
                            <a href="{{route('user.bulk-sms.contacts')}}">{{translate('Create in SMS Contacts')}}</a>
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
                    <label class="form-label">{{translate('Email Body')}} <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="body" rows="10" required
                        placeholder="{{translate('Enter your email content here...')}}">{{old('body')}}</textarea>
                    <small class="text-muted">
                        {{translate('Placeholders:')}} <code>{name}</code>, <code>{phone}</code>, <code>{email}</code>
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
