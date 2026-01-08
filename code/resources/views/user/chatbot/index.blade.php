@extends('layouts.master')
@section('content')

<div class="i-card-md">
    <div class="card-header">
        <h4 class="card-title">
            {{translate(Arr::get($meta_data,'title'))}}
        </h4>
        <div class="d-flex justify-content-end align-items-end gap-2">
            <a href="{{route('user.api-keys.index')}}" class="i-btn success btn--md capsuled">
                <i class="bi bi-key me-1"></i> {{translate('Manage API Keys')}}
            </a>
            <a href="{{route('user.chatbot.create')}}" class="i-btn primary btn--md capsuled">
                <i class="bi bi-plus-circle me-1"></i> {{translate('Create Chatbot')}}
            </a>
        </div>
    </div>

    <div class="card-body px-0">
        @if($chatbots->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{translate('Name')}}</th>
                            <th scope="col">{{translate('Domain')}}</th>
                            <th scope="col">{{translate('Status')}}</th>
                            <th scope="col">{{translate('Conversations')}}</th>
                            <th scope="col">{{translate('Training Data')}}</th>
                            <th scope="col">{{translate('Created')}}</th>
                            <th scope="col">{{translate('Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($chatbots as $chatbot)
                            <tr>
                                <td data-label="#">
                                    {{$loop->iteration}}
                                </td>
                                <td data-label='{{translate("Name")}}'>
                                    <strong>{{$chatbot->name}}</strong>
                                </td>
                                <td data-label='{{translate("Domain")}}'>
                                    {{$chatbot->domain ?? translate('Any')}}
                                </td>
                                <td data-label='{{translate("Status")}}'>
                                    @if($chatbot->status === 'active')
                                        <span class="badge badge--success">{{translate('Active')}}</span>
                                    @else
                                        <span class="badge badge--danger">{{translate('Inactive')}}</span>
                                    @endif
                                </td>
                                <td data-label='{{translate("Conversations")}}'>
                                    {{$chatbot->conversations_count ?? 0}}
                                </td>
                                <td data-label='{{translate("Training Data")}}'>
                                    {{$chatbot->training_data_count ?? 0}}
                                </td>
                                <td data-label='{{translate("Created")}}'>
                                    {{$chatbot->created_at->format('Y-m-d')}}
                                </td>
                                <td data-label='{{translate("Action")}}'>
                                    <div class="table-action">
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('Edit')}}"
                                            href="{{route('user.chatbot.edit', $chatbot->uid)}}" class="icon-btn info">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('Training')}}"
                                            href="{{route('user.chatbot.training', $chatbot->uid)}}" class="icon-btn primary">
                                            <i class="bi bi-book"></i>
                                        </a>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('Agents')}}"
                                            href="{{route('user.chatbot.agents', $chatbot->uid)}}" class="icon-btn success">
                                            <i class="bi bi-people"></i>
                                        </a>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('Analytics')}}"
                                            href="{{route('user.chatbot.analytics', $chatbot->uid)}}" class="icon-btn warning">
                                            <i class="bi bi-graph-up"></i>
                                        </a>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('Embed Code')}}"
                                            href="{{route('user.chatbot.embed', $chatbot->uid)}}" class="icon-btn secondary">
                                            <i class="bi bi-code-square"></i>
                                        </a>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('API Keys')}}"
                                            href="{{route('user.chatbot.api-keys', $chatbot->uid)}}" class="icon-btn dark">
                                            <i class="bi bi-key"></i>
                                        </a>
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{translate('Delete')}}"
                                            href="javascript:void(0);"
                                            onclick="if(confirm('{{translate('Are you sure?')}}')){window.location.href='{{route('user.chatbot.destroy', $chatbot->uid)}}'}"
                                            class="icon-btn danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-muted text-center" colspan="8">
                                    {{translate('No chatbots found')}}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="m-3">
                {{$chatbots->links()}}
            </div>
        @else
            @include('admin.partials.not_found')
        @endif
    </div>
</div>

@endsection
