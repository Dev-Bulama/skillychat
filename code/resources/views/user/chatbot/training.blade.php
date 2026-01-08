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
        <form action="{{route('user.chatbot.training.store', $chatbot->uid)}}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="type" class="form-label">{{translate('Type')}} <span class="text-danger">*</span></label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="text">{{translate('Text')}}</option>
                        <option value="url">{{translate('URL')}}</option>
                        <option value="file">{{translate('File')}}</option>
                        <option value="faq">{{translate('FAQ')}}</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="title" class="form-label">{{translate('Title')}}</label>
                    <input type="text" class="form-control" id="title" name="title" placeholder="{{translate('Optional title')}}">
                </div>

                <div class="col-md-6" id="content-field">
                    <label for="content" class="form-label">{{translate('Content')}} <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="3"></textarea>
                </div>

                <div class="col-md-6 d-none" id="url-field">
                    <label for="source_url" class="form-label">{{translate('URL')}} <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="source_url" name="source_url" placeholder="https://example.com">
                </div>

                <div class="col-md-6 d-none" id="file-field">
                    <label for="file" class="form-label">{{translate('File')}} <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="file" name="file" accept=".txt,.pdf,.doc,.docx">
                </div>

                <div class="col-md-12">
                    <button type="submit" class="i-btn primary btn--md capsuled">
                        <i class="bi bi-plus-circle me-1"></i> {{translate('Add Training Data')}}
                    </button>
                </div>
            </div>
        </form>

        <hr>

        <h5 class="mb-3">{{translate('Training Data List')}}</h5>

        @if($trainingData->count() > 0)
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{translate('Type')}}</th>
                            <th scope="col">{{translate('Title')}}</th>
                            <th scope="col">{{translate('Status')}}</th>
                            <th scope="col">{{translate('Created')}}</th>
                            <th scope="col">{{translate('Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($trainingData as $data)
                            <tr>
                                <td data-label="#">{{$loop->iteration}}</td>
                                <td data-label='{{translate("Type")}}'>
                                    <span class="badge badge--primary">{{ucfirst($data->type)}}</span>
                                </td>
                                <td data-label='{{translate("Title")}}'>{{$data->title ?? '-'}}</td>
                                <td data-label='{{translate("Status")}}'>
                                    @if($data->status === 'active')
                                        <span class="badge badge--success">{{translate('Active')}}</span>
                                    @elseif($data->status === 'processing')
                                        <span class="badge badge--warning">{{translate('Processing')}}</span>
                                    @else
                                        <span class="badge badge--danger">{{translate('Failed')}}</span>
                                    @endif
                                </td>
                                <td data-label='{{translate("Created")}}'>{{$data->created_at->format('Y-m-d')}}</td>
                                <td data-label='{{translate("Action")}}'>
                                    <a href="javascript:void(0);"
                                        onclick="if(confirm('{{translate('Are you sure?')}}')){window.location.href='{{route('user.chatbot.training.destroy', [$chatbot->uid, $data->id])}}'}"
                                        class="icon-btn danger">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="m-3">
                {{$trainingData->links()}}
            </div>
        @else
            @include('admin.partials.not_found')
        @endif
    </div>
</div>

@push('script-push')
<script nonce="{{ csp_nonce() }}">
    'use strict';

    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        const contentField = document.getElementById('content-field');
        const urlField = document.getElementById('url-field');
        const fileField = document.getElementById('file-field');

        contentField.classList.add('d-none');
        urlField.classList.add('d-none');
        fileField.classList.add('d-none');

        if (type === 'text' || type === 'faq') {
            contentField.classList.remove('d-none');
        } else if (type === 'url') {
            urlField.classList.remove('d-none');
        } else if (type === 'file') {
            fileField.classList.remove('d-none');
        }
    });
</script>
@endpush

@endsection
