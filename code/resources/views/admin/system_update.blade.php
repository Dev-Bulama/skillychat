@extends('admin.layouts.master')
@section('content')

@push('style-include')
<style nonce="{{csp_nonce()}}">
    /* Reset and base styles */
    .update-container * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .update-container {
        background-color: #ffffff;
        border-radius: 8px;
        width: 100%;
        max-width: 100%;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    h1 {
        font-size: 22px;
        margin-bottom: 15px;
        font-weight: 700;
        color: #2d3748;
        text-align: center;
    }

    /* Tab navigation styles */
    .tab-nav {
        display: flex;
        background-color: #f9fafb;
        border-radius: 8px;
        padding: 8px;
        margin-bottom: 24px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .tab-link {
        flex: 1;
        padding: 12px 16px;
        font-size: 17px;
        font-weight: 600;
        color: #4a5568;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 6px;
        position: relative;
    }

    .tab-link:hover {
        background-color: #e2e8f0;
        color: #2d3748;
    }

    .tab-link.active {
        background-color: #ffffff;
        color: #4299e1;
        box-shadow: 0 2px 4px rgba(66, 153, 225, 0.1);
    }

    .tab-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40%;
        height: 3px;
        background-color: #4299e1;
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    .tab-content {
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .tab-content.active {
        display: block;
        opacity: 1;
    }

    .update-status {
        margin-top: 15px;
        padding: 12px;
        border-radius: 5px;
        font-size: 14px;
        display: none;
        text-align: center;
    }

    .update-status.checking {
        display: block;
        background-color: #ebf8ff;
        color: #2b6cb0;
    }

    .update-status.available {
        display: block;
        background-color: #f0fff4;
        color: #276749;
    }

    .update-status.none {
        display: block;
        background-color: #fff5f5;
        color: #c53030;
    }

    .available-update-list {
        margin-top: 15px;
        display: none;
        flex-direction: column;
        gap: 24px;
    }

    .available-update-list.show {
        display: flex;
    }

    .update-card {
        background-color: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        transition: all 0.2s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .update-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .update-content {
        display: flex;
        flex-direction: column;
    }

    .update-info {
        flex: 1;
        padding: 32px;
    }

    .update-header {
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .update-version {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        letter-spacing: -0.025em;
        display: flex;
        align-items: center;
    }

    .update-version::after {
        content: "";
        display: inline-block;
        width: 6px;
        height: 6px;
        background-color: #4299e1;
        border-radius: 50%;
        margin-left: 16px;
        margin-right: 16px;
    }

    .update-date {
        font-size: 15px;
        color: #718096;
        font-weight: 500;
    }

    .update-notes {
        font-size: 15px;
        color: #718096;
        line-height: 1.7;
    }

    .update-action {
        padding: 24px;
        background: linear-gradient(to right, rgba(249, 250, 251, 0.5), rgba(249, 250, 251, 0.8));
        display: flex;
        justify-content: center;
        gap: 16px;
        border-top: 1px solid rgba(0, 0, 0, 0.03);
    }

    .download-button {
        background-color: #4299e1;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 18px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px -1px rgba(66, 153, 225, 0.2), 0 2px 4px -1px rgba(66, 153, 225, 0.1);
    }

    .download-button:hover {
        background-color: #3182ce;
        transform: translateY(-1px);
        box-shadow: 0 6px 10px -1px rgba(66, 153, 225, 0.3), 0 4px 6px -2px rgba(66, 153, 225, 0.15);
    }

    .download-button.disabled-button {
        background-color: #d1d5db;
        color: #6b7280;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .download-button.disabled-button:hover {
        background-color: #d1d5db;
        transform: none;
        box-shadow: none;
    }

    .changelog-button {
        background-color: transparent;
        color: #4299e1;
        border: 2px solid #4299e1;
        border-radius: 8px;
        padding: 8px 18px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all 0.2s ease;
    }

    .changelog-button:hover {
        background-color: #ebf8ff;
        color: #3182ce;
        border-color: #3182ce;
        transform: translateY(-1px);
    }

    .download-icon,
    .changelog-icon {
        margin-right: 16px;
        width: 18px;
        height: 18px;
    }

    .spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
        margin-right: 6px;
        display: none;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .checking .spinner {
        display: inline-block;
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background-color: #ffffff;
        border-radius: 12px;
        max-width: 600px;
        width: 90%;
        padding: 24px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        position: relative;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        font-size: 20px;
        color: #4a5568;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .modal-close:hover {
        color: #2d3748;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 16px;
    }

    .modal-body {
        font-size: 15px;
        color: #4a5568;
        line-height: 1.6;
    }

    /* Responsive styles */
    @media (min-width: 768px) {
        .update-content {
            flex-direction: row;
        }

        .update-header {
            display: flex;
            flex-direction: row;
            align-items: center;
        }

        .update-version::after {
            display: none;
        }

        .update-version {
            margin-right: 16px;
        }

        .update-action {
            display: flex;
            align-items: center;
            padding: 0 32px;
            min-width: 200px;
            border-top: none;
            border-left: 1px solid rgba(0, 0, 0, 0.03);
            background: linear-gradient(to left, rgba(249, 250, 251, 0.5), rgba(249, 250, 251, 0.8));
        }
    }

    /* Responsive styles for tabs */
    @media (max-width: 768px) {
        .tab-nav {
            flex-direction: column;
            padding: 6px;
        }

        .tab-link {
            padding: 10px 12px;
            font-size: 16px;
        }

        .tab-link.active::after {
            width: 60%;
        }

        .modal-content {
            width: 95%;
            padding: 16px;
        }
    }

    /* Minimal Version Display */
    .version-info {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .version-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }

    .version-number {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin: 0.5rem 0;
    }

    .version-date {
        font-size: 0.8rem;
        color: #9ca3af;
        margin: 0;
    }

    /* Hover effect */
    .version-info:hover {
        border-color: #d1d5db;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    /* Responsive */
    @media (max-width: 480px) {
        .version-info {
            max-width: 100%;
        }
    }

</style>
@endpush

<div class="container-fluid px-0">
    <div class="i-card-md mt-3">
        <div class="card--header">
            <h4 class="card-title">
                {{trans('default.system_update_title')}}
            </h4>
        </div>
        <div class="card-body">
            <ul class="update-list">
                @php echo (trans('default.update_note')) @endphp
            </ul>
        </div>
    </div>

    <div class="i-card-md mt-3">
        <div class="card--header">
            <h4 class="card-title">
                {{translate("System Update")}}
            </h4>
        </div>
        <div class="card-body">
            <div class="update-container">
                <div class="tab-nav">
                    <div class="tab-link active" data-tab="manual-update">{{translate("Manual Update")}}</div>
                    <div class="tab-link" data-tab="click-update">{{translate("Click & Update")}}</div>
                </div>
                <div class="tab-content active" id="manual-update">
                    <div class="version-info">
                        <span class="version-label">{{ translate("Current Version") }}</span>
                        <h4 class="version-number">{{ translate('V') }}{{ site_settings("app_version", 1.1) }}</h4>
                        <p class="version-date">{{ get_date_time(site_settings("system_installed_at", \Carbon\Carbon::now())) }}</p>
                    </div>
                    
                    <form action="{{route('admin.system.update')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mt-4 mb-4">
                            <label for="image" class="feedback-file">
                                <input name="updateFile" hidden accept=".zip" type="file" id="image">
                                <span><i class="bi bi-file-zip"></i> {{translate("Upload Zip file")}}</span>
                            </label>
                        </div>
                        <button class="i-btn btn--lg btn--primary update-btn" type="submit">
                            {{translate("Update Now")}}
                        </button>
                    </form>
                </div>
                <div class="tab-content" id="click-update">
                    <div class="update-status" id="updateStatus">
                        {{translate("Checking for updates...")}}
                    </div>
                    <div class="available-update-list" id="updateAvailableList">
                        <!-- Update items will be dynamically inserted here -->
                    </div>
                </div>
            </div>
            <!-- Modal for changelog -->
            <div class="modal" id="changelogModal">
                <div class="modal-content">
                    <span class="modal-close">&times;</span>
                    <h2 class="modal-title">{{translate("Changelog")}}</h2>
                    <div class="modal-body" id="changelogContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script-push')
<script nonce="{{ csp_nonce() }}">
    const currentAppVersion = "{{ site_settings('app_version', 1.1) }}";

</script>

<script nonce="{{ csp_nonce() }}">
    "use strict";
    $(document).ready(function() {

        const $updateStatus = $('#updateStatus');
        const $updateList = $('#updateAvailableList');
        const $spinner = $('#spinner');
        const $changelogModal = $('#changelogModal');
        const $changelogContent = $('#changelogContent');

        // Automatically trigger update check on page load
        checkupdate();

        function compareVersions(v1, v2) {
            const v1Parts = v1.split('.').map(Number);
            const v2Parts = v2.split('.').map(Number);
            for (let i = 0; i < Math.max(v1Parts.length, v2Parts.length); i++) {
                const part1 = v1Parts[i] || 0;
                const part2 = v2Parts[i] || 0;
                if (part1 < part2) return -1;
                if (part1 > part2) return 1;
            }
            return 0;
        }

        function checkupdate() {
            console.log('triggered');

            $updateStatus.attr('class', 'update-status checking');
            $updateStatus.text('{{translate("Checking for updates...")}}');
            $updateList.attr('class', 'available-update-list');
            $updateList.empty();
            $spinner.css('display', 'inline-block');

            $.ajax({
                url: '{{ route("admin.system.check.update") }}'
                , type: 'GET'
                , dataType: 'json'
                , success: function(response) {
                    console.log(response);

                    if (response.success && response.data && response.data.length > 0) {

                        const currentVersion = currentAppVersion;

                        // Sort updates by version number (ascending)
                        response.data.sort((a, b) => compareVersions(a.version, b.version));

                        // Find the next version
                        let nextVersion = null;
                        for (const update of response.data) {
                            if (compareVersions(update.version, currentVersion) > 0) {
                                nextVersion = update.version;
                                break;
                            }
                        }

                        $updateStatus.attr('class', 'update-status available');
                        $updateStatus.text(response.data.length + ' {{translate("updates available!")}}');
                        $updateList.attr('class', 'available-update-list show');
                        $.each(response.data, function(index, update) {
                            const isNextVersion = update.version === nextVersion;
                            const disabledAttr = isNextVersion ? '' : 'disabled';
                            const disabledClass = isNextVersion ? '' : 'disabled-button';
                            const updateItem = $('<div>', {
                                class: 'update-card'
                            });
                            updateItem.html(`
                                <div class="update-content">
                                    <div class="update-info">
                                        <div class="update-header">
                                            <span class="update-version"> V${update.version}</span>
                                            <span class="update-date">Release date : ${update.release_date}</span>
                                        </div>
                                    </div>
                                    <div class="update-action">
                                        <button class="download-button ${disabledClass}" data-version="${update.version}" ${disabledAttr}>
                                            <svg class="download-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                            </svg>
                                            {{translate("Download & Install")}}
                                        </button>
                                        <button class="changelog-button" data-changelog="${encodeURIComponent(update.changelog)}">
                                            <svg class="changelog-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                                <polyline points="10 9 9 9 8 9"></polyline>
                                            </svg>
                                            {{translate("View Changelog")}}
                                        </button>
                                    </div>
                                </div>
                            `);
                            $updateList.append(updateItem);
                        });
                        $('.download-button:not(.disabled-button)').on('click', function() {
                            const $button = $(this);
                            $button.prop('disabled', true).addClass('disabled-button');
                            const version = $button.data('version');
                            downloadAndInstallUpdate(version);
                        });
                        $('.changelog-button').on('click', function() {
                            const changelog = decodeURIComponent($(this).data('changelog'));
                            $changelogContent.html(changelog);
                            $changelogModal.addClass('active');
                        });
                    } else {
                        $updateStatus.attr('class', 'update-status text-success none');
                        $updateStatus.text('{{translate("Your software is up to date.")}}');
                    }
                }
                , error: function(xhr, status, error) {
                    $updateStatus.attr('class', 'update-status none');
                    $updateStatus.text('{{translate("Error checking for updates. Please try again later.")}}');
                    console.error('Update check failed:', error);
                }
                , complete: function() {
                    $spinner.css('display', 'none');
                }
            });
        }


        // Tab switching logic
        $('.tab-link').on('click', function() {
            const tabId = $(this).data('tab');
            $('.tab-link').removeClass('active');
            $('.tab-content').removeClass('active');
            $(this).addClass('active');
            $('#' + tabId).addClass('active');

            if (tabId == 'click-update') {
                checkupdate();
            }
        });



        // Modal close logic
        $('.modal-close, .modal').on('click', function(e) {
            if (e.target === this) {
                $changelogModal.removeClass('active');
            }
        });



        function downloadAndInstallUpdate(version) {
            $updateStatus.attr('class', 'update-status checking');
            $updateStatus.text('{{translate("Downloading and installing update")}} ' + version + '...');
            $.ajax({
                url: '{{ route("admin.system.install.update") }}'
                , type: 'POST'
                , data: {
                    version: version
                    , _token: '{{ csrf_token() }}'
                }
                , dataType: 'json'
                , success: function(response) {
                    if (response.success == true) {
                        $updateStatus.attr('class', 'update-status available');
                        $updateStatus.text('{{translate("Update installed successfully! Refreshing page...")}}');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        $updateStatus.attr('class', 'update-status none');
                        $updateStatus.text('{{translate("Update installation failed:")}} ' + response.message);
                    }
                }
                , error: function(xhr, status, error) {
                    $updateStatus.attr('class', 'update-status none');
                    $updateStatus.text('{{translate("Error installing update. Please try again later.")}}');
                    console.error('Update installation failed:', error);
                }
            });
        }
    });

</script>
@endpush

@endsection
