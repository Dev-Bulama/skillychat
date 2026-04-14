<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use App\Models\GoogleAccount;
use App\Models\GoogleLocation;
use App\Models\User;
use App\Models\Core\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GoogleBusinessAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permissions:view_settings'])->only(['settings', 'users']);
        $this->middleware(['permissions:update_settings'])->only(['saveSettings']);
    }

    /**
     * Show the Google Business settings page.
     */
    public function settings(): View
    {
        $connectedUsers = GoogleAccount::with(['user'])
            ->withCount(['locations'])
            ->latest()
            ->get();

        return view('admin.setting.google_business', [
            'title'          => 'Google Business Profile Settings',
            'breadcrumbs'    => ['home' => 'admin.home', 'Settings' => 'admin.setting.list', 'Google Business' => null],
            'connectedUsers' => $connectedUsers,
        ]);
    }

    /**
     * Save Google OAuth credentials and feature flag.
     */
    public function saveSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'google_oauth_client_id'     => 'nullable|string|max:500',
            'google_oauth_client_secret' => 'nullable|string|max:500',
            'google_oauth_redirect_uri'  => 'nullable|url|max:500',
        ]);

        $keys = [
            'google_oauth_client_id',
            'google_oauth_client_secret',
            'google_oauth_redirect_uri',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->input($key)]
                );
                Cache::forget('site_settings_' . $key);
            }
        }

        // Update feature flag
        $flag = FeatureFlag::where('name', 'google_business_profile')->first();
        if ($flag) {
            $flag->setEnabled((bool) $request->input('enabled', false));
        }

        return back()->with(response_status('Google Business settings saved successfully.'));
    }

    /**
     * List users who have connected their Google accounts.
     */
    public function users(): View
    {
        $accounts = GoogleAccount::with(['user'])
            ->withCount(['locations'])
            ->latest()
            ->paginate(paginateNumber());

        return view('admin.setting.google_business', [
            'title'       => 'Google Business – Connected Users',
            'breadcrumbs' => ['home' => 'admin.home', 'Google Business' => null],
            'accounts'    => $accounts,
        ]);
    }
}
