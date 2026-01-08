@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        @if($chatbot)
                            {{ translate('API Keys') }} - {{ $chatbot->name }}
                        @else
                            {{ translate('My AI Provider API Keys') }}
                        @endif
                    </h4>
                    <p class="text-muted mb-0">
                        {{ translate('Add your own API keys from OpenAI, Google, or Anthropic to use with your chatbots') }}
                    </p>
                </div>

                <div class="card-body">
                    @if(!$chatbot)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>{{ translate('How it works:') }}</strong>
                            <ul class="mb-0 mt-2">
                                <li>{{ translate('Add your API keys here to use them across all your chatbots') }}</li>
                                <li>{{ translate('Mark a key as "default" to use it automatically') }}</li>
                                <li>{{ translate('You can also add chatbot-specific keys in each chatbot settings') }}</li>
                                <li>{{ translate('If no key is provided, system fallback keys will be used (if available)') }}</li>
                            </ul>
                        </div>
                    @endif

                    <!-- Add New API Key Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">{{ translate('Add New API Key') }}</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ $chatbot ? route('user.chatbot.api-keys.store', $chatbot->uid) : route('user.api-keys.store') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="provider">{{ translate('AI Provider') }} <span class="text-danger">*</span></label>
                                            <select name="provider" id="provider" class="form-control" required>
                                                <option value="">{{ translate('Select Provider') }}</option>
                                                @foreach($providers as $key => $provider)
                                                    <option value="{{ $key }}">{{ $provider['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="key_name">{{ translate('Key Name') }} ({{ translate('Optional') }})</label>
                                            <input type="text" name="key_name" id="key_name" class="form-control" placeholder="e.g. Production Key">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="api_key">{{ translate('API Key') }} <span class="text-danger">*</span></label>
                                            <input type="password" name="api_key" id="api_key" class="form-control" placeholder="sk-..." required>
                                            <small class="form-text text-muted">{{ translate('Your key is encrypted and stored securely') }}</small>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            @if(!$chatbot)
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="is_default" name="is_default" value="1">
                                                    <label class="custom-control-label" for="is_default">{{ translate('Set as Default') }}</label>
                                                </div>
                                            @endif
                                            <button type="submit" class="btn btn-primary btn-block mt-2">
                                                <i class="fas fa-plus"></i> {{ translate('Add Key') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Provider Information -->
                    <div class="accordion mb-4" id="providerAccordion">
                        <div class="card">
                            <div class="card-header" id="providerInfoHeader">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#providerInfo">
                                        <i class="fas fa-question-circle"></i> {{ translate('How to get API keys?') }}
                                    </button>
                                </h5>
                            </div>
                            <div id="providerInfo" class="collapse" data-parent="#providerAccordion">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6><strong>OpenAI (GPT-4, GPT-3.5)</strong></h6>
                                            <ol class="small">
                                                <li>Visit <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com</a></li>
                                                <li>Sign up or log in</li>
                                                <li>Go to "API Keys" section</li>
                                                <li>Click "Create new secret key"</li>
                                                <li>Copy the key (starts with sk-)</li>
                                            </ol>
                                            <p class="small text-muted">Pricing: Pay as you go. ~$0.15-$2.50 per 1M tokens depending on model.</p>
                                        </div>
                                        <div class="col-md-4">
                                            <h6><strong>Google Gemini</strong></h6>
                                            <ol class="small">
                                                <li>Visit <a href="https://aistudio.google.com/app/apikey" target="_blank">aistudio.google.com</a></li>
                                                <li>Sign in with Google account</li>
                                                <li>Click "Get API key"</li>
                                                <li>Create or select a project</li>
                                                <li>Copy the API key</li>
                                            </ol>
                                            <p class="small text-muted">Pricing: Free tier available. Pro: ~$0.075-$1.25 per 1M tokens.</p>
                                        </div>
                                        <div class="col-md-4">
                                            <h6><strong>Anthropic Claude</strong></h6>
                                            <ol class="small">
                                                <li>Visit <a href="https://console.anthropic.com/" target="_blank">console.anthropic.com</a></li>
                                                <li>Sign up or log in</li>
                                                <li>Go to "API Keys"</li>
                                                <li>Click "Create Key"</li>
                                                <li>Copy the key</li>
                                            </ol>
                                            <p class="small text-muted">Pricing: ~$0.80-$15 per 1M tokens depending on model.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing API Keys -->
                    <h5 class="mb-3">{{ translate('Your API Keys') }}</h5>

                    @if($apiKeys->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> {{ translate('No API keys added yet. Add one above to get started.') }}
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ translate('Provider') }}</th>
                                        <th>{{ translate('Key Name') }}</th>
                                        <th>{{ translate('API Key') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                        <th>{{ translate('Default') }}</th>
                                        <th>{{ translate('Last Verified') }}</th>
                                        <th>{{ translate('Usage') }}</th>
                                        <th>{{ translate('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apiKeys as $key)
                                        <tr>
                                            <td>
                                                @if($key->provider === 'openai')
                                                    <span class="badge badge-primary">OpenAI</span>
                                                @elseif($key->provider === 'gemini')
                                                    <span class="badge badge-info">Google Gemini</span>
                                                @elseif($key->provider === 'claude')
                                                    <span class="badge badge-dark">Anthropic Claude</span>
                                                @endif
                                            </td>
                                            <td>{{ $key->key_name ?: '-' }}</td>
                                            <td>
                                                <code class="small">{{ $key->getMaskedKey() }}</code>
                                            </td>
                                            <td>
                                                @if($key->status === 'active')
                                                    <span class="badge badge-success">Active</span>
                                                @elseif($key->status === 'invalid')
                                                    <span class="badge badge-danger">Invalid</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($key->is_default)
                                                    <span class="badge badge-warning"><i class="fas fa-star"></i> Default</span>
                                                @else
                                                    <form method="POST" action="{{ $chatbot ? route('user.chatbot.api-keys.set-default', [$chatbot->uid, $key->id]) : route('user.api-keys.set-default', $key->id) }}" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ translate('Set as default') }}">
                                                            <i class="far fa-star"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                            <td>
                                                @if($key->last_verified_at)
                                                    {{ $key->last_verified_at->diffForHumans() }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <small>
                                                    {{ $key->total_requests }} requests<br>
                                                    @if($key->failed_requests > 0)
                                                        <span class="text-danger">{{ $key->failed_requests }} failed ({{ $key->getFailureRate() }}%)</span>
                                                    @else
                                                        <span class="text-success">0 failed</span>
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                <a href="{{ $chatbot ? route('user.chatbot.api-keys.destroy', [$chatbot->uid, $key->id]) : route('user.api-keys.destroy', $key->id) }}"
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('{{ translate('Are you sure you want to delete this API key?') }}')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Security Notice -->
                    <div class="alert alert-secondary mt-4">
                        <i class="fas fa-shield-alt"></i>
                        <strong>{{ translate('Security:') }}</strong>
                        {{ translate('All API keys are encrypted using AES-256 encryption before being stored in the database. Keys are only decrypted when needed to make API calls.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
