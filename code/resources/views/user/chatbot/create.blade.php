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
        <form action="{{route('user.chatbot.store')}}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">{{translate('Chatbot Name')}} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="{{old('name')}}" placeholder="{{translate('Enter chatbot name')}}" required>
                </div>

                <div class="col-md-6">
                    <label for="domain" class="form-label">{{translate('Domain')}}</label>
                    <input type="url" class="form-control" id="domain" name="domain"
                        value="{{old('domain')}}" placeholder="https://example.com">
                    <small class="text-muted">{{translate('Leave empty to allow all domains')}}</small>
                </div>

                <div class="col-md-12">
                    <label for="description" class="form-label">{{translate('Description')}}</label>
                    <textarea class="form-control" id="description" name="description" rows="3"
                        placeholder="{{translate('Enter chatbot description')}}">{{old('description')}}</textarea>
                </div>

                <div class="col-md-6">
                    <label for="language" class="form-label">{{translate('Language')}}</label>
                    <select class="form-select" id="language" name="language">
                        <option value="en" {{old('language') == 'en' ? 'selected' : ''}}>{{translate('English')}}</option>
                        <option value="es" {{old('language') == 'es' ? 'selected' : ''}}>{{translate('Spanish')}}</option>
                        <option value="fr" {{old('language') == 'fr' ? 'selected' : ''}}>{{translate('French')}}</option>
                        <option value="de" {{old('language') == 'de' ? 'selected' : ''}}>{{translate('German')}}</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="tone" class="form-label">{{translate('Tone')}}</label>
                    <select class="form-select" id="tone" name="tone">
                        <option value="professional" {{old('tone') == 'professional' ? 'selected' : ''}}>{{translate('Professional')}}</option>
                        <option value="friendly" {{old('tone') == 'friendly' ? 'selected' : ''}}>{{translate('Friendly')}}</option>
                        <option value="casual" {{old('tone') == 'casual' ? 'selected' : ''}}>{{translate('Casual')}}</option>
                        <option value="formal" {{old('tone') == 'formal' ? 'selected' : ''}}>{{translate('Formal')}}</option>
                    </select>
                </div>

                <div class="col-md-12">
                    <label for="welcome_message" class="form-label">{{translate('Welcome Message')}}</label>
                    <textarea class="form-control" id="welcome_message" name="welcome_message" rows="2"
                        placeholder="{{translate('Hello! How can I help you today?')}}">{{old('welcome_message')}}</textarea>
                </div>

                <div class="col-md-6">
                    <label for="ai_provider" class="form-label">{{translate('AI Provider')}}</label>
                    <select class="form-select" id="ai_provider" name="ai_provider">
                        <option value="openai" {{old('ai_provider') == 'openai' ? 'selected' : ''}}>{{translate('OpenAI')}}</option>
                        <option value="gemini" {{old('ai_provider') == 'gemini' ? 'selected' : ''}}>{{translate('Google Gemini')}}</option>
                        <option value="claude" {{old('ai_provider') == 'claude' ? 'selected' : ''}}>{{translate('Anthropic Claude')}}</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="widget_position" class="form-label">{{translate('Widget Position')}}</label>
                    <select class="form-select" id="widget_position" name="widget_position">
                        <option value="bottom-right" {{old('widget_position') == 'bottom-right' ? 'selected' : ''}}>{{translate('Bottom Right')}}</option>
                        <option value="bottom-left" {{old('widget_position') == 'bottom-left' ? 'selected' : ''}}>{{translate('Bottom Left')}}</option>
                        <option value="top-right" {{old('widget_position') == 'top-right' ? 'selected' : ''}}>{{translate('Top Right')}}</option>
                        <option value="top-left" {{old('widget_position') == 'top-left' ? 'selected' : ''}}>{{translate('Top Left')}}</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="bubble_style" class="form-label">{{translate('Chat Bubble Style')}}</label>
                    <select class="form-select" id="bubble_style" name="bubble_style">
                        <option value="classic" {{old('bubble_style', 'classic') == 'classic' ? 'selected' : ''}}>{{translate('Classic')}}</option>
                        <option value="modern" {{old('bubble_style') == 'modern' ? 'selected' : ''}}>{{translate('Modern Rounded')}}</option>
                        <option value="minimal" {{old('bubble_style') == 'minimal' ? 'selected' : ''}}>{{translate('Minimal Flat')}}</option>
                        <option value="gradient" {{old('bubble_style') == 'gradient' ? 'selected' : ''}}>{{translate('Gradient Flow')}}</option>
                    </select>
                    <small class="text-muted">{{translate('Choose the visual style for your chat bubble')}}</small>
                </div>

                <div class="col-md-12">
                    <label for="attention_message" class="form-label">{{translate('Attention Message')}} <small class="text-muted">({{translate('Optional')}})</small></label>
                    <input type="text" class="form-control" id="attention_message" name="attention_message"
                        value="{{old('attention_message')}}" placeholder="{{translate('e.g., Need help? Chat with us 👋')}}" maxlength="255">
                    <small class="text-muted">{{translate('A short message displayed on the chat bubble to grab attention')}}</small>
                </div>

                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emoji_support" name="emoji_support" value="1" {{old('emoji_support', true) ? 'checked' : ''}}>
                                <label class="form-check-label" for="emoji_support">{{translate('Emoji Support')}}</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="voice_support" name="voice_support" value="1" {{old('voice_support') ? 'checked' : ''}}>
                                <label class="form-check-label" for="voice_support">{{translate('Voice Support')}}</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="image_support" name="image_support" value="1" {{old('image_support') ? 'checked' : ''}}>
                                <label class="form-check-label" for="image_support">{{translate('Image Support')}}</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="human_takeover_enabled" name="human_takeover_enabled" value="1" {{old('human_takeover_enabled', true) ? 'checked' : ''}}>
                                <label class="form-check-label" for="human_takeover_enabled">{{translate('Human Takeover')}}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="i-btn primary btn--md capsuled">
                        <i class="bi bi-check-circle me-1"></i> {{translate('Create Chatbot')}}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
