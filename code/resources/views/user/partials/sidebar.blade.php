<aside class="aside">
    @php
    $user = auth_user('web');
    $subscription = $user->runningSubscription;
    $webhookAccess = @optional($subscription->package->social_access)->webhook_access;
    $accessPlatforms = (array) ($subscription ? @$subscription?->package?->social_access?->platform_access : []);
    $socialMediaEnabled  = \App\Models\FeatureFlag::enabled('social_media');
    $bulkSmsEnabled      = \App\Models\FeatureFlag::enabled('bulk_sms');
    $bulkEmailEnabled    = \App\Models\FeatureFlag::enabled('bulk_email');
    $whatsappEnabled     = \App\Models\FeatureFlag::enabled('whatsapp_chatbot');
    $leadCrmEnabled      = \App\Models\FeatureFlag::enabled('lead_crm');
    $dripEnabled         = \App\Models\FeatureFlag::enabled('drip_sequences');
    $invoiceEnabled      = \App\Models\FeatureFlag::enabled('invoices');
    $appointmentsEnabled = \App\Models\FeatureFlag::enabled('appointments');
    $googleBizEnabled    = \App\Models\FeatureFlag::enabled('google_business_profile');

    $platforms = get_platform()
    ->whereIn('id', $accessPlatforms )
    ->where("status",App\Enums\StatusEnum::true->status())
    ->where("is_integrated",App\Enums\StatusEnum::true->status());

    $platform = $platforms?->first()




    @endphp
    <div class="side-content">
        <a href="{{route('user.home')}}" class="sidebar-logo d-block">
            <div class="site-logo">
                <img class="img-fluid" src="{{imageURL(@site_logo('user_site_logo')->file,'user_site_logo',true)}}" alt="{{ @site_logo('user_site_logo')->file->name ?? 'site-logo.jpg'}}" />
            </div>
        </a>

        <div class="sidemenu-wrapper">
            <div class="sidebar-body" data-simplebar>
                <ul class="sidemenu-list">
                    <li class="side-menu-title">
                        {{translate("Main")}}
                    </li>

                    <li class="sidemenu-item">
                        <a href="{{route('user.home')}}" class="sidemenu-link {{request()->routeIs('user.home') ? 'active' :''}}">
                            <div class="sidemenu-icon">
                                <i class="bi bi-grid-1x2"></i>
                            </div>
                            <span>
                                {{translate('Dashboard')}}
                            </span>
                        </a>
                    </li>

                    @php
                    $lastSegment = collect(request()->segments())->last();
                    @endphp

                    @if($socialMediaEnabled)
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse
                            @if(request()->routeIs('user.social.post.*'))
                                active
                            @endif">
                            <div class="sidemenu-icon">
                                <i class="bi bi-stickies"></i>
                            </div>
                            <span>
                                {{translate("Post Feed")}}
                                <small><i class="bi bi-chevron-down"></i></small>
                            </span>
                        </a>

                        <div class="side-menu-dropdown @if(request()->routeIs('user.social.post.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.social.post.create') ? 'active' :''}}" href="{{route('user.social.post.create')}}">
                                        <span>
                                            <i class="bi bi-pencil-square"></i>
                                        </span>
                                        <p>
                                            {{translate('Create Post')}}
                                        </p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.social.post.show') || request()->routeIs('user.social.post.list')  ? 'active' :''}}" href="{{route('user.social.post.list')}}">
                                        <span>
                                            <i class="bi bi-newspaper"></i>
                                        </span>
                                        <p>
                                            {{translate('All Post')}}
                                        </p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse
                            @if(request()->routeIs('user.social.account.*'))
                                active
                            @endif">
                            <div class="sidemenu-icon">
                                <i class="bi bi-person-gear"></i>
                            </div>
                            <span>
                                {{translate("Social Accounts")}}
                                <small><i class="bi bi-chevron-down"></i></small>
                            </span>
                        </a>

                        <div class="side-menu-dropdown @if(request()->routeIs('user.social.account.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.social.account.list') || request()->routeIs('user.social.account.show') || request()->routeIs('user.social.account.create')   ? 'active' :''}}" href="{{ $platform ? route('user.social.account.list',['platform' => $platform->slug]) : route('user.social.account.list')    }}">
                                        <span>
                                            <i class="bi bi-person-lines-fill"></i>
                                        </span>
                                        <p>
                                            {{translate('Account list')}}
                                        </p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.social.account.platform')   ? 'active' :''}}" href="{{route('user.social.account.platform')}}">
                                        <span>
                                            <i class="bi bi-gear-wide-connected"></i>
                                        </span>
                                        <p>
                                            {{translate('Platform')}}
                                        </p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif {{-- end socialMediaEnabled --}}

                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse
                            @if(request()->routeIs('user.ai.content.list') || request()->routeIs('user.ai.content.image.list')||request()->routeIs('user.ai.content.video.list'))
                                active
                            @endif">
                            <div class="sidemenu-icon">
                                <i class="bi bi-play-btn"></i>
                            </div>
                            <span>
                                {{ translate("AI Contents") }}
                                <small><i class="bi bi-chevron-down"></i></small>
                            </span>
                        </a>

                        <div class="side-menu-dropdown @if(request()->routeIs('user.ai.content.list') || request()->routeIs('user.ai.content.image.list')||request()->routeIs('user.ai.content.video.list')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.ai.content.list') ? 'active' : '' }}" href="{{ route('user.ai.content.list') }}">
                                        <span><i class="bi bi-card-text"></i></span>
                                        <p>{{ translate('Text Contents') }}</p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.ai.content.image.list') ? 'active' : '' }}" href="{{ route('user.ai.content.image.list') }}">
                                        <span><i class="bi bi-image"></i></span>
                                        <p>{{ translate('Image Contents') }}</p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.ai.content.video.list') ? 'active' : '' }}" href="{{ route('user.ai.content.video.list') }}">
                                        <span><i class="bi bi-camera-video"></i></span>
                                        <p>{{ translate('Video Contents') }}</p>
                                    </a>
                                </li>


                            </ul>
                        </div>
                    </li>


                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse
                            @if(request()->routeIs('user.ai.content.image.gallery') || request()->routeIs('user.ai.content.video.gallery'))
                                active
                            @endif">
                            <div class="sidemenu-icon">
                                <i class="bi bi-collection-play"></i>
                            </div>
                            <span>
                                {{ translate("AI Galleries") }}
                                <small><i class="bi bi-chevron-down"></i></small>
                            </span>
                        </a>

                        <div class="side-menu-dropdown @if(request()->routeIs('user.ai.content.image.gallery') || request()->routeIs('user.ai.content.video.gallery')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.ai.content.image.gallery') ? 'active' : '' }}" href="{{ route('user.ai.content.image.gallery') }}">
                                        <span><i class="bi bi-images"></i></span>
                                        <p>{{ translate('Image Gallery') }}</p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.ai.content.video.gallery') ? 'active' : '' }}" href="{{ route('user.ai.content.video.gallery') }}">
                                        <span><i class="bi bi-film"></i></span>
                                        <p>{{ translate('Video Gallery') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse
                            @if(request()->routeIs('user.chatbot.*'))
                                active
                            @endif">
                            <div class="sidemenu-icon">
                                <i class="bi bi-robot"></i>
                            </div>
                            <span>
                                {{ translate("AI Chatbots") }}
                                <small><i class="bi bi-chevron-down"></i></small>
                            </span>
                        </a>

                        <div class="side-menu-dropdown @if(request()->routeIs('user.chatbot.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.chatbot.index') || request()->routeIs('user.chatbot.show') ? 'active' : '' }}" href="{{ route('user.chatbot.index') }}">
                                        <span><i class="bi bi-list-ul"></i></span>
                                        <p>{{ translate('My Chatbots') }}</p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.chatbot.create') ? 'active' : '' }}" href="{{ route('user.chatbot.create') }}">
                                        <span><i class="bi bi-plus-circle"></i></span>
                                        <p>{{ translate('Create Chatbot') }}</p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.api-keys.index') ? 'active' : '' }}" href="{{ route('user.api-keys.index') }}">
                                        <span><i class="bi bi-key"></i></span>
                                        <p>{{ translate('API Keys') }}</p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.live-agent.*') ? 'active' : '' }}" href="{{ route('user.live-agent.dashboard') }}">
                                        <span><i class="bi bi-headset"></i></span>
                                        <p>
                                            {{ translate('Live Agent') }}
                                            @php $pendingCount = auth_user('web') ? \App\Models\ChatbotConversation::whereIn('chatbot_id', \App\Models\Chatbot::where('user_id', auth_user('web')->id)->pluck('id'))->where('status','human_requested')->count() : 0; @endphp
                                            @if($pendingCount > 0)
                                                <span class="badge bg-danger ms-1" style="font-size:.65rem;">{{ $pendingCount }}</span>
                                            @endif
                                        </p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Social Media Boost --}}
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.smm.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-rocket-takeoff"></i></div>
                            <span>{{translate("Social Media Boost")}} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.smm.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.smm.index') ? 'active' :''}}" href="{{route('user.smm.index')}}">
                                        <span><i class="bi bi-grid"></i></span><p>{{translate('Browse Services')}}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.smm.orders') ? 'active' :''}}" href="{{route('user.smm.orders')}}">
                                        <span><i class="bi bi-list-check"></i></span><p>{{translate('My Orders')}}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.smm.how-it-works') ? 'active' :''}}" href="{{route('user.smm.how-it-works')}}">
                                        <span><i class="bi bi-question-circle"></i></span><p>{{translate('How It Works')}}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    @if($bulkSmsEnabled)
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.bulk-sms.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-phone"></i></div>
                            <span>{{translate("Bulk SMS")}} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.bulk-sms.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.bulk-sms.index') ? 'active' :''}}" href="{{route('user.bulk-sms.index')}}">
                                        <span><i class="bi bi-list-ul"></i></span><p>{{translate('Campaigns')}}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.bulk-sms.create') ? 'active' :''}}" href="{{route('user.bulk-sms.create')}}">
                                        <span><i class="bi bi-plus-circle"></i></span><p>{{translate('New Campaign')}}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.bulk-sms.contacts*') ? 'active' :''}}" href="{{route('user.bulk-sms.contacts')}}">
                                        <span><i class="bi bi-people"></i></span><p>{{translate('Contacts')}}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.bulk-sms.provider*') ? 'active' :''}}" href="{{route('user.bulk-sms.provider')}}">
                                        <span><i class="bi bi-gear"></i></span><p>{{translate('SMS Provider')}}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(\App\Models\FeatureFlag::enabled('google_business_profile'))
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.google-business.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-building" style="color:#4285f4;"></i></div>
                            <span>{{ translate('Google Business') }} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.google-business.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.google-business.index') ? 'active' : '' }}" href="{{ route('user.google-business.index') }}">
                                        <span><i class="bi bi-geo-alt"></i></span><p>{{ translate('My Locations') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if($whatsappEnabled)
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.whatsapp.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-whatsapp" style="color:#25d366;"></i></div>
                            <span>{{ translate('WhatsApp') }} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.whatsapp.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.whatsapp.index') ? 'active' : '' }}" href="{{ route('user.whatsapp.index') }}">
                                        <span><i class="bi bi-phone"></i></span><p>{{ translate('Accounts') }}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.whatsapp.create') ? 'active' : '' }}" href="{{ route('user.whatsapp.create') }}">
                                        <span><i class="bi bi-plus-circle"></i></span><p>{{ translate('Add Account') }}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.whatsapp.documentation') ? 'active' : '' }}" href="{{ route('user.whatsapp.documentation') }}">
                                        <span><i class="bi bi-book"></i></span><p>{{ translate('Setup Guide') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if($leadCrmEnabled)
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.crm.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-kanban" style="color:#6d28d9;"></i></div>
                            <span>{{ translate('Lead CRM') }} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.crm.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.crm.index') ? 'active' : '' }}" href="{{ route('user.crm.index') }}">
                                        <span><i class="bi bi-layout-three-columns"></i></span><p>{{ translate('Kanban Board') }}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.crm.leads') ? 'active' : '' }}" href="{{ route('user.crm.leads') }}">
                                        <span><i class="bi bi-people"></i></span><p>{{ translate('All Leads') }}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.crm.create') ? 'active' : '' }}" href="{{ route('user.crm.create') }}">
                                        <span><i class="bi bi-person-plus"></i></span><p>{{ translate('Add Lead') }}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.crm.pipeline') ? 'active' : '' }}" href="{{ route('user.crm.pipeline') }}">
                                        <span><i class="bi bi-gear"></i></span><p>{{ translate('Pipeline Setup') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if($dripEnabled)
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.drip.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-funnel" style="color:#dc2626;"></i></div>
                            <span>{{ translate('Drip Sequences') }} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.drip.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.drip.index') ? 'active' : '' }}" href="{{ route('user.drip.index') }}">
                                        <span><i class="bi bi-list-ul"></i></span><p>{{ translate('My Sequences') }}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.drip.create') ? 'active' : '' }}" href="{{ route('user.drip.create') }}">
                                        <span><i class="bi bi-plus-circle"></i></span><p>{{ translate('New Sequence') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if($invoiceEnabled)
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.invoice.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-receipt" style="color:#0ea5e9;"></i></div>
                            <span>{{ translate('Invoices') }} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.invoice.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.invoice.index') ? 'active' : '' }}" href="{{ route('user.invoice.index') }}">
                                        <span><i class="bi bi-list-ul"></i></span><p>{{ translate('All Invoices') }}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.invoice.create') ? 'active' : '' }}" href="{{ route('user.invoice.create') }}">
                                        <span><i class="bi bi-plus-circle"></i></span><p>{{ translate('New Invoice') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if($appointmentsEnabled)
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.appointments.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-calendar-check" style="color:#0ea5e9;"></i></div>
                            <span>{{ translate('Appointments') }} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.appointments.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.appointments.index') ? 'active' : '' }}" href="{{ route('user.appointments.index') }}">
                                        <span><i class="bi bi-list-ul"></i></span><p>{{ translate('All Appointments') }}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.appointments.create') ? 'active' : '' }}" href="{{ route('user.appointments.create') }}">
                                        <span><i class="bi bi-plus-circle"></i></span><p>{{ translate('New Appointment') }}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.appointments.services') ? 'active' : '' }}" href="{{ route('user.appointments.services') }}">
                                        <span><i class="bi bi-briefcase"></i></span><p>{{ translate('Services') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if($googleBizEnabled)
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.google-business.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-building" style="color:#4285f4;"></i></div>
                            <span>{{ translate('Google Business') }} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.google-business.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{ request()->routeIs('user.google-business.index') ? 'active' : '' }}" href="{{ route('user.google-business.index') }}">
                                        <span><i class="bi bi-building-check"></i></span><p>{{ translate('My Locations') }}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if($bulkEmailEnabled)
                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class="sidemenu-link sidemenu-collapse @if(request()->routeIs('user.bulk-email.*')) active @endif">
                            <div class="sidemenu-icon"><i class="bi bi-envelope-paper"></i></div>
                            <span>{{translate("Bulk Email")}} <small><i class="bi bi-chevron-down"></i></small></span>
                        </a>
                        <div class="side-menu-dropdown @if(request()->routeIs('user.bulk-email.*')) show-sideMenu @endif">
                            <ul class="sub-menu">
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.bulk-email.index') ? 'active' :''}}" href="{{route('user.bulk-email.index')}}">
                                        <span><i class="bi bi-list-ul"></i></span><p>{{translate('Campaigns')}}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.bulk-email.create') ? 'active' :''}}" href="{{route('user.bulk-email.create')}}">
                                        <span><i class="bi bi-plus-circle"></i></span><p>{{translate('New Campaign')}}</p>
                                    </a>
                                </li>
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.bulk-email.provider*') ? 'active' :''}}" href="{{route('user.bulk-email.provider')}}">
                                        <span><i class="bi bi-gear"></i></span><p>{{translate('Email Provider')}}</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    <li class="sidemenu-item">
                        <a href="{{route('user.plan')}}" class="sidemenu-link  {{request()->routeIs('user.plan') ? 'active' :''}}">
                            <div class="sidemenu-icon" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-custom-class="custom-tooltip" data-bs-title="{{translate('Plans')}}">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <span>
                                {{translate("Plans")}}
                            </span>
                        </a>
                    </li>

                    <li class="sidemenu-item">
                        <a href="javascript:void(0)" class='sidemenu-link sidemenu-collapse
                                @if($lastSegment == "reports")
                                    active
                                @endif'>
                            <div class="sidemenu-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <span>
                                {{translate("Reports")}} <small><i class="bi bi-chevron-down"></i></small>
                            </span>
                        </a>

                        <div class="side-menu-dropdown  @if($lastSegment == 'reports')
                                    show-sideMenu
                                @endif">
                            <ul class="sub-menu">

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.subscription.report.*') ? 'active' :''}}" href="{{route('user.subscription.report.list')}}">
                                        <span>
                                            <i class="bi bi-bookmarks"></i>
                                        </span>

                                        <p>
                                            {{translate('Subscription Reports')}}
                                        </p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.credit.report.*') ? 'active' :''}}" href="{{route('user.credit.report.list')}}">
                                        <span><i class="bi bi-credit-card-2-front"></i></span>
                                        <p>
                                            {{translate('Credit Reports')}}
                                        </p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link  {{request()->routeIs('user.deposit.report.*') ? 'active' :''}}" href="{{route('user.deposit.report.list')}}">
                                        <span><i class="bi bi-wallet"></i></span>
                                        <p>
                                            {{translate('Deposit Reports')}}
                                        </p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.withdraw.report.*') ? 'active' :''}}" href="{{route('user.withdraw.report.list')}}">
                                        <span><i class="bi bi-box-arrow-in-up-left"></i></span>
                                        <p>
                                            {{translate('Withdraw Reports')}}
                                        </p>
                                    </a>
                                </li>

                                @if(site_settings("affiliate_system") == App\Enums\StatusEnum::true->status())
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.affiliate.report.*') ? 'active' :''}}" href="{{route('user.affiliate.report.list')}}">
                                        <span><i class="bi bi-share"></i></span>
                                        <p>
                                            {{translate('Affiliate Reports')}}
                                        </p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.affiliate.user.*') ? 'active' :''}}" href="{{route('user.affiliate.user.list')}}">
                                        <span><i class="bi bi-people"></i></span>
                                        <p>
                                            {{translate('Affiliate Users')}}
                                        </p>
                                    </a>
                                </li>
                                @endif

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.transaction.report.*') ? 'active' :''}}" href="{{route('user.transaction.report.list')}}">
                                        <span><i class="bi bi-arrow-left-right"></i></span>
                                        <p>{{ translate('Transaction Reports' )}}</p>
                                    </a>
                                </li>

                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.kyc.report.*') ? 'active' :''}}" href="{{route('user.kyc.report.list')}}">
                                        <span><i class="bi bi-shield-lock"></i></span>
                                        <p>
                                            {{translate('KYC Reports')}}
                                        </p>
                                    </a>
                                </li>

                                @if($webhookAccess == App\Enums\StatusEnum::true->status())
                                <li class="sub-menu-item">
                                    <a class="sidebar-menu-link {{request()->routeIs('user.webhook.report.*') ? 'active' :''}}" href="{{route('user.webhook.report.list')}}">
                                        <span><i class="bi bi-bell"></i></span>
                                        <p>
                                            {{translate('Webhook Reports')}}
                                        </p>
                                    </a>
                                </li>
                                @endif

                            </ul>
                        </div>
                    </li>

                    <li class="sidemenu-item">
                        <a href="{{route('user.ticket.list')}}" class="sidemenu-link  {{request()->routeIs('user.ticket.*') ? 'active' :''}}">
                            <div class="sidemenu-icon">
                                <i class="bi bi-patch-question"></i>
                            </div>

                            <span>
                                {{translate("Tickets")}}
                            </span>
                        </a>
                    </li>

                    @if(site_settings('user_docs_content'))
                    <li class="sidemenu-item">
                        <a href="{{ route('user.documentation') }}" target="_blank" class="sidemenu-link {{ request()->routeIs('user.documentation') ? 'active' : '' }}">
                            <div class="sidemenu-icon">
                                <i class="bi bi-book"></i>
                            </div>
                            <span>{{ translate(site_settings('user_docs_label') ?: 'Documentation') }}</span>
                        </a>
                    </li>
                    @endif

                    <li class="side-menu-title">
                        {{translate('Setting')}}
                    </li>

                    <li class="sidemenu-item">
                        <a href="{{route('user.profile')}}" class="sidemenu-link  {{request()->routeIs('user.profile') ? 'active' :''}}">
                            <div class="sidemenu-icon">
                                <i class="bi bi-gear"></i>
                            </div>

                            <span>
                                {{translate("Profile")}}
                            </span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-footer">
                <div class="header-right-item">
                    <div class="dropdown currency">
                        <button class="dropdown-toggle" type="button" @if($currencies->count() > 0)
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            @endif>
                            {{session()->get('currency')?->code}}
                        </button>

                        @if($currencies->count() > 0)
                        <ul class="dropdown-menu dropdown-menu-end">

                            @foreach($currencies as $currency)
                            <li>
                                <a class="dropdown-item" href="{{route('currency.change',$currency->code)}}"> {{$currency->code}}</a>
                            </li>
                            @endforeach

                        </ul>
                        @endif
                    </div>
                </div>

                <div class="total-balance">
                    <h4>
                        {{num_format(number:$user->balance,calC:true)}}
                    </h4>
                    <p>
                        {{translate('Total Balance')}}
                    </p>
                </div>
                <a href="{{route('user.logout')}}"><span><i class="bi bi-box-arrow-right"></i></span>
                    {{translate('Logout')}}
                </a>
            </div>
        </div>
    </div>
</aside>
