@extends('admin.layouts.master')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">
                        <i class="las la-cog"></i> {{translate('Theme Configuration')}} - {{$theme->name}}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{route('admin.theme.update.config', $theme->id)}}" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Colors Section -->
                            <div class="col-12">
                                <h5 class="mb-3">{{translate('Colors')}}</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label for="primary_color">{{translate('Primary Color')}}</label>
                                    <input type="color" name="config[colors][primary]"
                                           value="{{$theme->getConfig('colors.primary', '#4F46E5')}}"
                                           class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label for="secondary_color">{{translate('Secondary Color')}}</label>
                                    <input type="color" name="config[colors][secondary]"
                                           value="{{$theme->getConfig('colors.secondary', '#10B981')}}"
                                           class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label for="accent_color">{{translate('Accent Color')}}</label>
                                    <input type="color" name="config[colors][accent]"
                                           value="{{$theme->getConfig('colors.accent', '#F59E0B')}}"
                                           class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label for="text_color">{{translate('Text Color')}}</label>
                                    <input type="color" name="config[colors][text]"
                                           value="{{$theme->getConfig('colors.text', '#1F2937')}}"
                                           class="form-control">
                                </div>
                            </div>

                            <!-- Fonts Section -->
                            <div class="col-12 mt-4">
                                <h5 class="mb-3">{{translate('Typography')}}</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label for="heading_font">{{translate('Heading Font')}}</label>
                                    <select name="config[fonts][heading]" class="form-select">
                                        <option value="Inter, sans-serif" {{$theme->getConfig('fonts.heading') === 'Inter, sans-serif' ? 'selected' : ''}}>Inter</option>
                                        <option value="Roboto, sans-serif" {{$theme->getConfig('fonts.heading') === 'Roboto, sans-serif' ? 'selected' : ''}}>Roboto</option>
                                        <option value="Poppins, sans-serif" {{$theme->getConfig('fonts.heading') === 'Poppins, sans-serif' ? 'selected' : ''}}>Poppins</option>
                                        <option value="Montserrat, sans-serif" {{$theme->getConfig('fonts.heading') === 'Montserrat, sans-serif' ? 'selected' : ''}}>Montserrat</option>
                                        <option value="Space Grotesk, sans-serif" {{$theme->getConfig('fonts.heading') === 'Space Grotesk, sans-serif' ? 'selected' : ''}}>Space Grotesk</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label for="body_font">{{translate('Body Font')}}</label>
                                    <select name="config[fonts][body]" class="form-select">
                                        <option value="Inter, sans-serif" {{$theme->getConfig('fonts.body') === 'Inter, sans-serif' ? 'selected' : ''}}>Inter</option>
                                        <option value="Roboto, sans-serif" {{$theme->getConfig('fonts.body') === 'Roboto, sans-serif' ? 'selected' : ''}}>Roboto</option>
                                        <option value="Open Sans, sans-serif" {{$theme->getConfig('fonts.body') === 'Open Sans, sans-serif' ? 'selected' : ''}}>Open Sans</option>
                                        <option value="Lato, sans-serif" {{$theme->getConfig('fonts.body') === 'Lato, sans-serif' ? 'selected' : ''}}>Lato</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Layout Section -->
                            <div class="col-12 mt-4">
                                <h5 class="mb-3">{{translate('Layout')}}</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label for="header_style">{{translate('Header Style')}}</label>
                                    <select name="config[layout][header_style]" class="form-select">
                                        <option value="modern" {{$theme->getConfig('layout.header_style') === 'modern' ? 'selected' : ''}}>{{translate('Modern')}}</option>
                                        <option value="minimal" {{$theme->getConfig('layout.header_style') === 'minimal' ? 'selected' : ''}}>{{translate('Minimal')}}</option>
                                        <option value="classic" {{$theme->getConfig('layout.header_style') === 'classic' ? 'selected' : ''}}>{{translate('Classic')}}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-inner">
                                    <label for="footer_style">{{translate('Footer Style')}}</label>
                                    <select name="config[layout][footer_style]" class="form-select">
                                        <option value="full" {{$theme->getConfig('layout.footer_style') === 'full' ? 'selected' : ''}}>{{translate('Full')}}</option>
                                        <option value="compact" {{$theme->getConfig('layout.footer_style') === 'compact' ? 'selected' : ''}}>{{translate('Compact')}}</option>
                                        <option value="minimal" {{$theme->getConfig('layout.footer_style') === 'minimal' ? 'selected' : ''}}>{{translate('Minimal')}}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="i-btn btn--md btn--primary">
                                    <i class="las la-save"></i> {{translate('Save Configuration')}}
                                </button>
                                <a href="{{route('admin.theme.index')}}" class="i-btn btn--md btn--secondary">
                                    <i class="las la-arrow-left"></i> {{translate('Back to Themes')}}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="i-card-md">
                <div class="card--header">
                    <h4 class="card-title">
                        <i class="las la-info-circle"></i> {{translate('Theme Information')}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>{{translate('Name')}}:</strong> {{$theme->name}}
                    </div>
                    <div class="mb-3">
                        <strong>{{translate('Version')}}:</strong> {{$theme->version}}
                    </div>
                    @if($theme->author)
                        <div class="mb-3">
                            <strong>{{translate('Author')}}:</strong> {{$theme->author}}
                        </div>
                    @endif
                    <div class="mb-3">
                        <strong>{{translate('Status')}}:</strong>
                        @if($theme->status === 'active')
                            <span class="badge bg-success">{{translate('Active')}}</span>
                        @else
                            <span class="badge bg-secondary">{{translate('Inactive')}}</span>
                        @endif
                    </div>
                    @if($theme->is_system)
                        <div class="alert alert-info">
                            <i class="las la-shield-alt"></i> {{translate('This is a system theme and cannot be deleted.')}}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
