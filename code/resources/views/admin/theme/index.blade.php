@extends('admin.layouts.master')

@push('style-include')
<style nonce="{{csp_nonce()}}">
    .theme-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
        margin-top: 24px;
    }

    .theme-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .theme-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        transform: translateY(-4px);
    }

    .theme-card.active {
        border-color: #4F46E5;
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.2);
    }

    .theme-preview {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .theme-preview.pro {
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
    }

    .theme-preview.minimal {
        background: linear-gradient(135deg, #1F2937 0%, #4B5563 100%);
    }

    .theme-preview-icon {
        font-size: 64px;
        color: rgba(255, 255, 255, 0.9);
    }

    .theme-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: rgba(255, 255, 255, 0.95);
        color: #10B981;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .theme-badge.system {
        background: rgba(79, 70, 229, 0.95);
        color: white;
    }

    .theme-info {
        padding: 20px;
    }

    .theme-name {
        font-size: 18px;
        font-weight: 600;
        color: #1F2937;
        margin-bottom: 8px;
    }

    .theme-description {
        font-size: 14px;
        color: #6B7280;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    .theme-meta {
        display: flex;
        gap: 16px;
        margin-bottom: 16px;
        font-size: 12px;
        color: #9CA3AF;
    }

    .theme-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .theme-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .theme-btn {
        flex: 1;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .theme-btn-primary {
        background: #4F46E5;
        color: white;
    }

    .theme-btn-primary:hover {
        background: #4338CA;
    }

    .theme-btn-success {
        background: #10B981;
        color: white;
        pointer-events: none;
    }

    .theme-btn-secondary {
        background: #F3F4F6;
        color: #4B5563;
    }

    .theme-btn-secondary:hover {
        background: #E5E7EB;
    }

    .theme-btn-danger {
        background: #EF4444;
        color: white;
    }

    .theme-btn-danger:hover {
        background: #DC2626;
    }

    .upload-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        margin-bottom: 24px;
    }

    .upload-area {
        border: 2px dashed #D1D5DB;
        border-radius: 8px;
        padding: 32px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .upload-area:hover {
        border-color: #4F46E5;
        background: #F9FAFB;
    }

    .upload-icon {
        font-size: 48px;
        color: #9CA3AF;
        margin-bottom: 16px;
    }

    .upload-text {
        font-size: 16px;
        color: #4B5563;
        margin-bottom: 8px;
    }

    .upload-subtext {
        font-size: 14px;
        color: #9CA3AF;
    }

    @media (max-width: 768px) {
        .theme-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Upload Theme Section -->
    <div class="upload-section">
        <h5 class="mb-3">
            <i class="las la-upload"></i> {{translate('Install New Theme')}}
        </h5>
        <form action="{{route('admin.theme.install')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="upload-area">
                <i class="las la-cloud-upload-alt upload-icon"></i>
                <div class="upload-text">{{translate('Drop your theme ZIP file here or click to browse')}}</div>
                <div class="upload-subtext">{{translate('Maximum file size: 50MB')}}</div>
                <input type="file" name="theme_file" accept=".zip" style="display: none;" id="theme-file-input">
                <button type="button" class="i-btn btn--md btn--primary mt-3" onclick="document.getElementById('theme-file-input').click()">
                    <i class="las la-folder-open"></i> {{translate('Select File')}}
                </button>
            </div>
            <div class="mt-3 text-end">
                <button type="submit" class="i-btn btn--md btn--success" id="upload-btn" disabled>
                    <i class="las la-upload"></i> {{translate('Install Theme')}}
                </button>
            </div>
        </form>
    </div>

    <!-- Themes Grid -->
    <div class="i-card-md">
        <div class="card--header">
            <h4 class="card-title">
                <i class="las la-palette"></i> {{translate('Installed Themes')}}
            </h4>
        </div>
        <div class="card-body">
            <div class="theme-grid">
                @forelse($themes as $theme)
                    <div class="theme-card {{$theme->status === 'active' ? 'active' : ''}}">
                        <div class="theme-preview {{$theme->slug}}">
                            <i class="las la-palette theme-preview-icon"></i>
                            @if($theme->status === 'active')
                                <span class="theme-badge">{{translate('Active')}}</span>
                            @elseif($theme->is_system)
                                <span class="theme-badge system">{{translate('System')}}</span>
                            @endif
                        </div>
                        <div class="theme-info">
                            <h3 class="theme-name">{{$theme->name}}</h3>
                            <p class="theme-description">{{$theme->description}}</p>

                            <div class="theme-meta">
                                <div class="theme-meta-item">
                                    <i class="las la-code-branch"></i>
                                    <span>v{{$theme->version}}</span>
                                </div>
                                @if($theme->author)
                                    <div class="theme-meta-item">
                                        <i class="las la-user"></i>
                                        <span>{{$theme->author}}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="theme-actions">
                                @if($theme->status === 'active')
                                    <button class="theme-btn theme-btn-success" disabled>
                                        <i class="las la-check-circle"></i>
                                        {{translate('Active')}}
                                    </button>
                                @else
                                    <button class="theme-btn theme-btn-primary activate-theme" data-id="{{$theme->id}}">
                                        <i class="las la-check"></i>
                                        {{translate('Activate')}}
                                    </button>
                                @endif

                                <a href="{{route('admin.theme.configure', $theme->id)}}" class="theme-btn theme-btn-secondary">
                                    <i class="las la-cog"></i>
                                    {{translate('Configure')}}
                                </a>

                                @if(!$theme->is_system && $theme->status !== 'active')
                                    <button class="theme-btn theme-btn-danger delete-theme" data-id="{{$theme->id}}">
                                        <i class="las la-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="las la-palette" style="font-size: 64px; color: #D1D5DB;"></i>
                        <p class="text-muted">{{translate('No themes installed')}}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-push')
<script nonce="{{ csp_nonce() }}">
(function($) {
    "use strict";

    // Handle file selection
    $('#theme-file-input').on('change', function() {
        if (this.files.length > 0) {
            const fileName = this.files[0].name;
            $('.upload-text').text(fileName);
            $('#upload-btn').prop('disabled', false);
        }
    });

    // Activate theme
    $('.activate-theme').on('click', function() {
        const themeId = $(this).data('id');
        const button = $(this);

        button.prop('disabled', true);

        $.ajax({
            url: '{{route("admin.theme.activate")}}',
            type: 'POST',
            data: {
                theme_id: themeId,
                _token: '{{csrf_token()}}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                    button.prop('disabled', false);
                }
            },
            error: function() {
                alert('{{translate("Failed to activate theme")}}');
                button.prop('disabled', false);
            }
        });
    });

    // Delete theme
    $('.delete-theme').on('click', function() {
        if (!confirm('{{translate("Are you sure you want to delete this theme?")}}')) {
            return;
        }

        const themeId = $(this).data('id');
        const button = $(this);

        button.prop('disabled', true);

        $.ajax({
            url: '{{route("admin.theme.delete")}}',
            type: 'POST',
            data: {
                theme_id: themeId,
                _token: '{{csrf_token()}}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                    button.prop('disabled', false);
                }
            },
            error: function() {
                alert('{{translate("Failed to delete theme")}}');
                button.prop('disabled', false);
            }
        });
    });
})(jQuery);
</script>
@endpush
