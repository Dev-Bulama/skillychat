<form  class="settingsForm" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="i-card-md">
        <div class="card--header">
            <h4 class="card-title">
                  {{  Arr::get($tab,'title') }}
            </h4>
        </div>
        <div class="card-body">
            @foreach($socail_login_settings  as $medium => $settings)
                <div class="mb-10">
                    <h6>
                        {{ ucWords(str_replace('_',' ',$medium))}}
                    </h6>
                    <div class="mt-30">
                        @php
                            $social_settings = ($settings);
                        @endphp
                        <div class="row">
                            @foreach( $settings as $key => $val)
                                <div class="col-xl-6">
                                    <div class="form-inner">
                                        <label for="{{$key}}-{{$medium}}-{{$loop->index}}">
                                            {{
                                                Str::ucfirst(str_replace("_"," ",$key))
                                            }}  <small class="text-danger" >*</small>
                                        </label>
                                        @if($key == 'status')
                                            <select class="form-select" name="site_settings[social_login_with][{{$medium}}][{{$key}}]" id="{{$key}}-{{$medium}}-{{$loop->index}}">
                                                <option {{$val == App\Enums\StatusEnum::true->status() ? "selected" :""}} value="{{App\Enums\StatusEnum::true->status()}}">
                                                    {{translate('Active')}}
                                                </option>
                                                <option {{$val == App\Enums\StatusEnum::false->status() ? "selected" :""}} value="{{App\Enums\StatusEnum::false->status()}}">
                                                    {{translate('Inactive')}}
                                                </option>
                                            </select>
                                        @else
                                            <input id="{{$key}}-{{$medium}}-{{$loop->index}}" required  value="{{is_demo() ? '@@@' :$val}}" name='site_settings[social_login_with][{{$medium}}][{{$key}}]' placeholder="************" type="text">
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="col-xl-6">
                                <div class="form-inner">
                                    <label for="oauth_method-{{$medium}}">
                                        {{translate('OAuth Method')}}
                                        <span class="text-info" data-bs-toggle="tooltip" title="{{translate('PKCE (Proof Key for Code Exchange) is more secure and recommended for modern OAuth implementations')}}">
                                            <i class="las la-info-circle"></i>
                                        </span>
                                    </label>
                                    <select class="form-select" name="site_settings[social_login_with][{{$medium}}][oauth_method]" id="oauth_method-{{$medium}}">
                                        <option {{(@$settings['oauth_method'] ?? 'pkce') == 'pkce' ? "selected" :""}} value="pkce">
                                            {{translate('PKCE (Recommended)')}}
                                        </option>
                                        <option {{(@$settings['oauth_method'] ?? 'pkce') == 'plain' ? "selected" :""}} value="plain">
                                            {{translate('Plain')}}
                                        </option>
                                    </select>
                                    <small class="text-muted">{{translate('Select OAuth flow method for this provider')}}</small>
                                </div>
                            </div>

                            <div class="col-xl-6">
                                <div class="form-inner">
                                    <label for="callbackUrl-{{$medium}}-{{$key}}">
                                        {{translate('Callback URL')}}
                                        <span class="text-info" data-bs-toggle="tooltip" title="{{translate('You can edit this URL if you need a custom callback endpoint')}}">
                                            <i class="las la-info-circle"></i>
                                        </span>
                                    </label>
                                    <div class="input-group">
                                        <input id="callbackUrl-{{$medium}}-{{$key}}"
                                               name="site_settings[social_login_with][{{$medium}}][callback_url]"
                                               value='{{@$settings["callback_url"] ?? route("auth.social.login.callback",str_replace("_oauth","",$medium))}}'
                                               type="text"
                                               class="form-control"
                                               placeholder="{{route('auth.social.login.callback',str_replace('_oauth','',$medium))}}">
                                        <span class="input-group-text pointer copy-text" data-text='{{@$settings["callback_url"] ?? route("auth.social.login.callback",str_replace("_oauth","",$medium))}}'>
                                            <i class="las la-copy"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted">{{translate('Enter this URL in your OAuth provider settings')}}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            <div>
                <button type="submit" class="i-btn ai-btn btn--md btn--primary" data-anim="ripple">
                    {{translate("Submit")}}
                </button>
            </div>
        </div>
    </div>
</form>