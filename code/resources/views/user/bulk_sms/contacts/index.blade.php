@extends('layouts.master')
@section('content')

<div class="row g-3">
    {{-- Groups Panel --}}
    <div class="col-md-4">
        <div class="i-card-md">
            <div class="card-header">
                <h5 class="card-title">{{translate('Contact Groups')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{route('user.bulk-sms.groups.store')}}" method="POST" class="mb-3">
                    @csrf
                    <div class="input-group">
                        <input type="text" class="form-control" name="name" placeholder="{{translate('Group name')}}" required>
                        <button class="i-btn primary btn--md" type="submit">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </form>

                <ul class="list-group list-group-flush">
                    @forelse($groups as $group)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <a href="?group_id={{$group->id}}" class="text-decoration-none {{$selectedGroup == $group->id ? 'fw-bold text-primary' : ''}}">
                            {{$group->name}}
                            <span class="badge bg-secondary ms-1">{{$group->contacts_count}}</span>
                        </a>
                        <a href="{{route('user.bulk-sms.groups.destroy', $group->id)}}"
                           onclick="return confirm('{{translate('Delete this group and ungroup its contacts?')}}')"
                           class="text-danger"><i class="bi bi-trash-fill"></i></a>
                    </li>
                    @empty
                    <li class="list-group-item text-muted px-0">{{translate('No groups yet.')}}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Import CSV --}}
        <div class="i-card-md mt-3">
            <div class="card-header"><h5 class="card-title">{{translate('Import CSV')}}</h5></div>
            <div class="card-body">
                <form action="{{route('user.bulk-sms.contacts.import')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">{{translate('CSV File')}}</label>
                        <input type="file" class="form-control" name="csv_file" accept=".csv,.txt" required>
                        <small class="text-muted">{{translate('Columns: phone, name (optional), email (optional)')}}</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">{{translate('Add to Group')}}</label>
                        <select class="form-select" name="sms_contact_group_id">
                            <option value="">{{translate('No group')}}</option>
                            @foreach($groups as $group)
                            <option value="{{$group->id}}">{{$group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="i-btn success btn--md w-100">
                        <i class="bi bi-upload me-1"></i> {{translate('Import')}}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Contacts Table --}}
    <div class="col-md-8">
        <div class="i-card-md">
            <div class="card-header">
                <h5 class="card-title">{{translate('Contacts')}}</h5>
            </div>
            <div class="card-body">
                {{-- Add Contact Form --}}
                <form action="{{route('user.bulk-sms.contacts.store')}}" method="POST" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="phone" placeholder="{{translate('Phone *')}}" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="name" placeholder="{{translate('Name')}}">
                    </div>
                    <div class="col-md-3">
                        <input type="email" class="form-control" name="email" placeholder="{{translate('Email')}}">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="sms_contact_group_id">
                            <option value="">{{translate('Group')}}</option>
                            @foreach($groups as $group)
                            <option value="{{$group->id}}" {{$selectedGroup == $group->id ? 'selected':''}}>{{$group->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button class="i-btn primary btn--md w-100" type="submit">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </form>

                @if($contacts->count() > 0)
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>{{translate('Phone')}}</th>
                                <th>{{translate('Name')}}</th>
                                <th>{{translate('Group')}}</th>
                                <th>{{translate('Subscribed')}}</th>
                                <th>{{translate('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contacts as $contact)
                            <tr>
                                <td>{{$contact->phone}}</td>
                                <td>{{$contact->name ?? '—'}}</td>
                                <td>{{$contact->group?->name ?? '—'}}</td>
                                <td>
                                    <span class="badge badge--{{$contact->is_subscribed ? 'success' : 'danger'}}">
                                        {{$contact->is_subscribed ? translate('Yes') : translate('No')}}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{route('user.bulk-sms.contacts.destroy', $contact->id)}}"
                                       onclick="return confirm('{{translate('Delete contact?')}}')"
                                       class="i-btn danger btn--sm">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pt-2">{{$contacts->links()}}</div>
                @else
                <p class="text-muted text-center py-4">{{translate('No contacts found.')}}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
