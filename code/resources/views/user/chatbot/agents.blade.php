@extends('layouts.master')
@section('content')

<div class="i-card-md">
    <div class="card-header">
        <h4 class="card-title">
            {{translate(Arr::get($meta_data,'title'))}}
        </h4>
        <a href="{{route('user.chatbot.index')}}" class="i-btn danger btn--md">
            <i class="bi bi-arrow-left me-1"></i> {{translate('Back')}}
        </a>
    </div>

    <div class="card-body">
        <form action="{{route('user.chatbot.agents.store', $chatbot->uid)}}" method="POST">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="name" class="form-label">{{translate('Name')}} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="col-md-4">
                    <label for="email" class="form-label">{{translate('Email')}} <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="col-md-4">
                    <label for="role" class="form-label">{{translate('Role')}} <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="agent">{{translate('Agent')}}</option>
                        <option value="admin">{{translate('Admin')}}</option>
                        <option value="viewer">{{translate('Viewer')}}</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="can_takeover" name="can_takeover" value="1" checked>
                        <label class="form-check-label" for="can_takeover">{{translate('Can Takeover Conversations')}}</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="auto_assign" name="auto_assign" value="1">
                        <label class="form-check-label" for="auto_assign">{{translate('Auto Assign Conversations')}}</label>
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="i-btn primary btn--md capsuled">
                        <i class="bi bi-plus-circle me-1"></i> {{translate('Add Agent')}}
                    </button>
                </div>
            </div>
        </form>

        <hr>

        <h5 class="mb-3">{{translate('Agents List')}}</h5>

        @if($agents->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{translate('Name')}}</th>
                            <th scope="col">{{translate('Email')}}</th>
                            <th scope="col">{{translate('Role')}}</th>
                            <th scope="col">{{translate('Status')}}</th>
                            <th scope="col">{{translate('Conversations')}}</th>
                            <th scope="col">{{translate('Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agents as $agent)
                            <tr>
                                <td data-label="#">{{$loop->iteration}}</td>
                                <td data-label='{{translate("Name")}}'>{{$agent->name}}</td>
                                <td data-label='{{translate("Email")}}'>{{$agent->email}}</td>
                                <td data-label='{{translate("Role")}}'>
                                    <span class="badge badge--primary">{{ucfirst($agent->role)}}</span>
                                </td>
                                <td data-label='{{translate("Status")}}'>
                                    @if($agent->status === 'online')
                                        <span class="badge badge--success">{{translate('Online')}}</span>
                                    @elseif($agent->status === 'away')
                                        <span class="badge badge--warning">{{translate('Away')}}</span>
                                    @elseif($agent->status === 'busy')
                                        <span class="badge badge--info">{{translate('Busy')}}</span>
                                    @else
                                        <span class="badge badge--secondary">{{translate('Offline')}}</span>
                                    @endif
                                </td>
                                <td data-label='{{translate("Conversations")}}'>{{$agent->total_conversations_handled}}</td>
                                <td data-label='{{translate("Action")}}'>
                                    @if($agent->role !== 'admin')
                                        <a href="javascript:void(0);"
                                            onclick="if(confirm('{{translate('Are you sure?')}}')){window.location.href='{{route('user.chatbot.agents.destroy', [$chatbot->uid, $agent->id])}}'}"
                                            class="icon-btn danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="m-3">
                {{$agents->links()}}
            </div>
        @else
            @include('admin.partials.not_found')
        @endif
    </div>
</div>

@endsection
