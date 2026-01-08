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
        <div class="alert alert-info">
            <h5 class="alert-heading">{{translate('How to Install')}}</h5>
            <p>{{translate('Copy the code below and paste it just before the closing </body> tag on your website.')}}</p>
        </div>

        <div class="mb-4">
            <label class="form-label">{{translate('Embed Code')}}</label>
            <div class="position-relative">
                <pre class="bg-light p-3 rounded" style="position: relative;"><code id="embed-code">{{$embedCode}}</code></pre>
                <button class="btn btn-sm btn-primary position-absolute" style="top: 10px; right: 10px;" onclick="copyEmbedCode()">
                    <i class="bi bi-clipboard"></i> {{translate('Copy')}}
                </button>
            </div>
        </div>

        <div class="mb-4">
            <h5>{{translate('Installation Options')}}</h5>
            <ul class="list-group">
                <li class="list-group-item">
                    <strong>{{translate('WordPress')}}</strong>
                    <p class="mb-0 text-muted">{{translate('Install using a plugin like "Insert Headers and Footers" or add directly to your theme\'s footer.php file.')}}</p>
                </li>
                <li class="list-group-item">
                    <strong>{{translate('Shopify')}}</strong>
                    <p class="mb-0 text-muted">{{translate('Go to Online Store > Themes > Edit Code > theme.liquid and paste before </body>.')}}</p>
                </li>
                <li class="list-group-item">
                    <strong>{{translate('HTML Website')}}</strong>
                    <p class="mb-0 text-muted">{{translate('Add the code to every page where you want the chatbot to appear, just before the closing </body> tag.')}}</p>
                </li>
                <li class="list-group-item">
                    <strong>{{translate('React/Vue/Angular')}}</strong>
                    <p class="mb-0 text-muted">{{translate('Add the script tag to your index.html file in the public folder.')}}</p>
                </li>
            </ul>
        </div>

        <div class="mb-4">
            <h5>{{translate('Chatbot Information')}}</h5>
            <table class="table">
                <tr>
                    <th width="200">{{translate('Chatbot ID')}}</th>
                    <td><code>{{$chatbot->uid}}</code></td>
                </tr>
                <tr>
                    <th>{{translate('Status')}}</th>
                    <td>
                        @if($chatbot->status === 'active')
                            <span class="badge badge--success">{{translate('Active')}}</span>
                        @else
                            <span class="badge badge--danger">{{translate('Inactive')}}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{translate('Domain')}}</th>
                    <td>{{$chatbot->domain ?? translate('Any domain allowed')}}</td>
                </tr>
                <tr>
                    <th>{{translate('AI Provider')}}</th>
                    <td>{{ucfirst($chatbot->ai_provider)}}</td>
                </tr>
                <tr>
                    <th>{{translate('Created')}}</th>
                    <td>{{$chatbot->created_at->format('Y-m-d H:i:s')}}</td>
                </tr>
            </table>
        </div>

        <div class="alert alert-warning">
            <h5 class="alert-heading">{{translate('Important Notes')}}</h5>
            <ul class="mb-0">
                <li>{{translate('Make sure your chatbot status is set to "Active" for the widget to work.')}}</li>
                <li>{{translate('If you specified a domain, the widget will only work on that domain.')}}</li>
                <li>{{translate('The widget will automatically match your brand colors and settings.')}}</li>
                <li>{{translate('You can customize the appearance and behavior in the chatbot settings.')}}</li>
            </ul>
        </div>
    </div>
</div>

@push('script-push')
<script nonce="{{ csp_nonce() }}">
    'use strict';

    function copyEmbedCode() {
        const code = document.getElementById('embed-code').textContent;
        navigator.clipboard.writeText(code).then(() => {
            alert('{{translate("Embed code copied to clipboard!")}}');
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }
</script>
@endpush

@endsection
