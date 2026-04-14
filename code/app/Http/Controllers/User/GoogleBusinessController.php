<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SyncGoogleBusinessInfoJob;
use App\Jobs\SyncGooglePostsJob;
use App\Jobs\SyncGoogleReviewsJob;
use App\Models\GoogleAccount;
use App\Models\GoogleLocation;
use App\Models\GooglePost;
use App\Models\GoogleReview;
use App\Models\GoogleSyncLog;
use App\Services\Google\GoogleBusinessService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoogleBusinessController extends Controller
{
    protected $user;
    protected GoogleBusinessService $service;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user    = auth_user('web');
            $this->service = new GoogleBusinessService();
            return $next($request);
        });
    }

    // ────────────────────────────────────────────────────────────────────────
    // Index
    // ────────────────────────────────────────────────────────────────────────

    public function index(): View
    {
        $account   = GoogleAccount::where('user_id', $this->user->id)->first();
        $locations = $account
            ? GoogleLocation::where('user_id', $this->user->id)
                ->where('google_account_id', $account->id)
                ->latest()
                ->get()
            : collect();

        return view('user.google_business.index', [
            'meta_data' => $this->metaData(['title' => 'Google Business Profile']),
            'account'   => $account,
            'locations' => $locations,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // OAuth
    // ────────────────────────────────────────────────────────────────────────

    public function redirect(): RedirectResponse
    {
        $url = $this->service->getAuthUrl((string) $this->user->id);
        return redirect()->away($url);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('user.google-business.index')
                ->with(response_status('Google authorisation was denied: ' . $request->input('error'), 'error'));
        }

        $code = $request->input('code');
        if (! $code) {
            return redirect()->route('user.google-business.index')
                ->with(response_status('No authorisation code received.', 'error'));
        }

        // Exchange code for tokens
        $tokenResult = $this->service->exchangeCode($code);
        if (! $tokenResult['success']) {
            return redirect()->route('user.google-business.index')
                ->with(response_status($tokenResult['error'], 'error'));
        }

        $tokens = $tokenResult['data'];

        // Get Google user info
        $userInfoResult = $this->service->getUserInfo($tokens['access_token']);
        $googleSub      = $userInfoResult['success'] ? ($userInfoResult['data']['sub'] ?? null) : null;
        $googleEmail    = $userInfoResult['success'] ? ($userInfoResult['data']['email'] ?? null) : null;

        // Upsert GoogleAccount (deduplicate by google_sub if possible, else by user_id)
        $existing = GoogleAccount::where('user_id', $this->user->id)->first();

        $accountData = [
            'google_email' => $googleEmail,
            'google_sub'   => $googleSub,
            'access_token' => encrypt($tokens['access_token']),
            'token_expiry' => Carbon::now()->addSeconds($tokens['expires_in'] ?? 3600),
            'status'       => 'connected',
        ];

        if (! empty($tokens['refresh_token'])) {
            $accountData['refresh_token'] = encrypt($tokens['refresh_token']);
        }

        if ($existing) {
            $existing->update($accountData);
            $googleAccount = $existing;
        } else {
            $accountData['user_id'] = $this->user->id;
            $googleAccount = GoogleAccount::create($accountData);
        }

        // Fetch GMB accounts and locations
        $accountsResult = $this->service->getAccounts($googleAccount);

        if ($accountsResult['success'] && ! empty($accountsResult['data'])) {
            $firstAccount = $accountsResult['data'][0];
            $accountName  = $firstAccount['name'] ?? null;

            if ($accountName) {
                $locationsResult = $this->service->getLocations($googleAccount, $accountName);

                if ($locationsResult['success']) {
                    foreach ($locationsResult['data'] as $loc) {
                        $this->upsertLocation($googleAccount, $accountName, $loc);
                    }
                }
            }
        }

        GoogleSyncLog::record($this->user->id, 'oauth_connect', 'success', null, 200, 'Connected Google account');

        return redirect()->route('user.google-business.index')
            ->with(response_status('Google Business Profile connected successfully.'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Disconnect
    // ────────────────────────────────────────────────────────────────────────

    public function disconnect(): RedirectResponse
    {
        $account = GoogleAccount::where('user_id', $this->user->id)->first();

        if ($account) {
            // Attempt to revoke token
            $token = $account->getPlainAccessToken();
            if ($token) {
                try {
                    \Illuminate\Support\Facades\Http::post(
                        'https://oauth2.googleapis.com/revoke',
                        ['token' => $token]
                    );
                } catch (\Throwable $e) {
                    // Ignore revoke errors
                }
            }

            $account->locations()->delete();
            $account->delete();
        }

        return redirect()->route('user.google-business.index')
            ->with(response_status('Google Business account disconnected.'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Sync locations
    // ────────────────────────────────────────────────────────────────────────

    public function syncLocations(): RedirectResponse
    {
        $account = GoogleAccount::where('user_id', $this->user->id)->firstOrFail();

        $accountsResult = $this->service->getAccounts($account);

        if (! $accountsResult['success']) {
            return back()->with(response_status($accountsResult['error'], 'error'));
        }

        $synced = 0;
        foreach ($accountsResult['data'] as $gmbAccount) {
            $accountName     = $gmbAccount['name'] ?? null;
            if (! $accountName) continue;

            $locationsResult = $this->service->getLocations($account, $accountName);
            if ($locationsResult['success']) {
                foreach ($locationsResult['data'] as $loc) {
                    $this->upsertLocation($account, $accountName, $loc);
                    $synced++;
                }
            }
        }

        GoogleSyncLog::record($this->user->id, 'sync_locations', 'success', null, 200, "Synced {$synced} locations");

        return back()->with(response_status("Synced {$synced} location(s) from Google."));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Location detail / edit
    // ────────────────────────────────────────────────────────────────────────

    public function showLocation(int $id): View
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        return view('user.google_business.location', [
            'meta_data' => $this->metaData(['title' => 'Edit Location – ' . $location->display_name]),
            'location'  => $location,
        ]);
    }

    public function updateLocation(Request $request, int $id): RedirectResponse
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'phone'        => 'nullable|string|max:50',
            'website'      => 'nullable|url|max:500',
            'description'  => 'nullable|string|max:750',
            'hours'        => 'nullable|array',
        ]);

        // Build payload for Google API
        $googlePayload = [
            'title'       => $validated['display_name'],
            'websiteUri'  => $validated['website'] ?? null,
            'profile'     => ['description' => $validated['description'] ?? ''],
        ];

        if (! empty($validated['phone'])) {
            $googlePayload['phoneNumbers'] = ['primaryPhone' => $validated['phone']];
        }

        if (! empty($validated['hours'])) {
            $googlePayload['regularHours'] = $this->buildHoursPayload($validated['hours']);
        }

        $account = GoogleAccount::where('id', $location->google_account_id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $result = $this->service->updateLocation($account, $location->location_name, $googlePayload);

        if (! $result['success']) {
            return back()->with(response_status($result['error'], 'error'));
        }

        // Persist locally
        $location->update([
            'display_name' => $validated['display_name'],
            'phone'        => $validated['phone'] ?? $location->phone,
            'website'      => $validated['website'] ?? $location->website,
            'description'  => $validated['description'] ?? $location->description,
            'hours_json'   => $validated['hours'] ? $this->buildHoursPayload($validated['hours']) : $location->hours_json,
            'last_sync_at' => now(),
        ]);

        GoogleSyncLog::record($this->user->id, 'update_location', 'success', $location->id, 200);

        return back()->with(response_status('Business information updated successfully.'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Reviews
    // ────────────────────────────────────────────────────────────────────────

    public function reviews(int $id): View
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $filter  = request('filter', 'all');
        $query   = GoogleReview::where('location_id', $location->id);

        switch ($filter) {
            case 'needs_reply':
                $query->where('status', 'pending');
                break;
            case 'low':
                $query->whereIn('star_rating', [1, 2]);
                break;
            case 'high':
                $query->whereBetween('star_rating', [3, 5]);
                break;
        }

        $reviews = $query->latest('create_time')->paginate(paginateNumber());

        return view('user.google_business.reviews', [
            'meta_data' => $this->metaData(['title' => 'Reviews – ' . $location->display_name]),
            'location'  => $location,
            'reviews'   => $reviews,
            'filter'    => $filter,
        ]);
    }

    public function replyReview(Request $request, int $id, string $reviewName): RedirectResponse
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $validated = $request->validate(['reply' => 'required|string|max:4096']);

        $review = GoogleReview::where('location_id', $location->id)
            ->where('review_name', urldecode($reviewName))
            ->firstOrFail();

        $account = GoogleAccount::where('id', $location->google_account_id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $result = $this->service->replyToReview($account, $review->review_name, $validated['reply']);

        if (! $result['success']) {
            return back()->with(response_status($result['error'], 'error'));
        }

        $review->update([
            'reply'      => $validated['reply'],
            'reply_time' => now(),
            'status'     => 'replied',
        ]);

        return back()->with(response_status('Reply posted successfully.'));
    }

    public function deleteReviewReply(int $id, string $reviewName): RedirectResponse
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $review = GoogleReview::where('location_id', $location->id)
            ->where('review_name', urldecode($reviewName))
            ->firstOrFail();

        $account = GoogleAccount::where('id', $location->google_account_id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $result = $this->service->deleteReviewReply($account, $review->review_name);

        if (! $result['success']) {
            return back()->with(response_status($result['error'], 'error'));
        }

        $review->update(['reply' => null, 'reply_time' => null, 'status' => 'pending']);

        return back()->with(response_status('Reply deleted.'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Posts
    // ────────────────────────────────────────────────────────────────────────

    public function posts(int $id): View
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $posts = GooglePost::where('location_id', $location->id)
            ->latest('create_time')
            ->paginate(paginateNumber());

        return view('user.google_business.posts', [
            'meta_data' => $this->metaData(['title' => 'Posts – ' . $location->display_name]),
            'location'  => $location,
            'posts'     => $posts,
        ]);
    }

    public function createPost(Request $request, int $id): RedirectResponse
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'summary'              => 'required|string|max:1500',
            'call_to_action_type'  => 'nullable|string|max:50',
            'call_to_action_url'   => 'nullable|url|max:500',
        ]);

        $payload = ['summary' => $validated['summary'], 'topicType' => 'STANDARD'];

        if (! empty($validated['call_to_action_type'])) {
            $payload['callToAction'] = [
                'actionType' => $validated['call_to_action_type'],
            ];
            if (! empty($validated['call_to_action_url'])) {
                $payload['callToAction']['url'] = $validated['call_to_action_url'];
            }
        }

        $account = GoogleAccount::where('id', $location->google_account_id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $result = $this->service->createPost($account, $location->location_name, $payload);

        if (! $result['success']) {
            return back()->with(response_status($result['error'], 'error'));
        }

        $data = $result['data'];

        GooglePost::create([
            'location_id'          => $location->id,
            'user_id'              => $this->user->id,
            'post_name'            => $data['name'] ?? null,
            'topic_type'           => $data['topicType'] ?? 'STANDARD',
            'summary'              => $validated['summary'],
            'call_to_action_type'  => $validated['call_to_action_type'] ?? null,
            'call_to_action_url'   => $validated['call_to_action_url'] ?? null,
            'state'                => $data['state'] ?? 'LIVE',
            'create_time'          => isset($data['createTime']) ? Carbon::parse($data['createTime']) : now(),
        ]);

        return back()->with(response_status('Post created successfully.'));
    }

    public function deletePost(int $id, int $postId): RedirectResponse
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $post = GooglePost::where('id', $postId)
            ->where('location_id', $location->id)
            ->firstOrFail();

        $account = GoogleAccount::where('id', $location->google_account_id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        if ($post->post_name) {
            $result = $this->service->deletePost($account, $post->post_name);
            if (! $result['success']) {
                return back()->with(response_status($result['error'], 'error'));
            }
        }

        $post->delete();

        return back()->with(response_status('Post deleted.'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Insights
    // ────────────────────────────────────────────────────────────────────────

    public function insights(int $id): View
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $days      = (int) request('days', 30);
        $endDate   = Carbon::now()->toIso8601String();
        $startDate = Carbon::now()->subDays($days)->toIso8601String();

        $account      = GoogleAccount::where('id', $location->google_account_id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        $insightsResult = $this->service->getInsights($account, $location->location_name, $startDate, $endDate);

        $insightsData = $insightsResult['success'] ? ($insightsResult['data'] ?? []) : [];

        return view('user.google_business.insights', [
            'meta_data'    => $this->metaData(['title' => 'Insights – ' . $location->display_name]),
            'location'     => $location,
            'insightsData' => $insightsData,
            'days'         => $days,
            'error'        => $insightsResult['success'] ? null : $insightsResult['error'],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Manual sync (dispatch jobs)
    // ────────────────────────────────────────────────────────────────────────

    public function syncReviews(int $id): RedirectResponse
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        SyncGoogleReviewsJob::dispatch($location->id);

        return back()->with(response_status('Review sync queued.'));
    }

    public function syncPosts(int $id): RedirectResponse
    {
        $location = GoogleLocation::where('id', $id)
            ->where('user_id', $this->user->id)
            ->firstOrFail();

        SyncGooglePostsJob::dispatch($location->id);

        return back()->with(response_status('Post sync queued.'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Insert or update a location record from Google API response.
     */
    private function upsertLocation(GoogleAccount $googleAccount, string $accountName, array $loc): void
    {
        $locationName = $loc['name'] ?? null;
        if (! $locationName) return;

        $phone   = $loc['phoneNumbers']['primaryPhone'] ?? null;
        $website = $loc['websiteUri'] ?? null;
        $desc    = $loc['profile']['description'] ?? null;

        $existing = GoogleLocation::where('location_name', $locationName)
            ->where('user_id', $this->user->id)
            ->first();

        $data = [
            'user_id'           => $this->user->id,
            'google_account_id' => $googleAccount->id,
            'account_name'      => $accountName,
            'location_name'     => $locationName,
            'display_name'      => $loc['title'] ?? $locationName,
            'phone'             => $phone,
            'website'           => $website,
            'address_json'      => $loc['storefrontAddress'] ?? null,
            'hours_json'        => $loc['regularHours'] ?? null,
            'description'       => $desc,
            'categories_json'   => $loc['categories'] ?? null,
            'is_verified'       => isset($loc['metadata']['isVerified']) ? (int) $loc['metadata']['isVerified'] : 0,
            'last_sync_at'      => now(),
        ];

        if ($existing) {
            $existing->update($data);
        } else {
            GoogleLocation::create($data);
        }
    }

    /**
     * Convert the hours form input into the Google regularHours format.
     */
    private function buildHoursPayload(array $hours): array
    {
        $periods = [];
        $days    = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];
        $keys    = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($keys as $i => $key) {
            if (! empty($hours[$key]['open'])) {
                $periods[] = [
                    'openDay'   => $days[$i],
                    'closeDay'  => $days[$i],
                    'openTime'  => ['hours' => (int) explode(':', $hours[$key]['open_time'] ?? '09:00')[0], 'minutes' => (int) (explode(':', $hours[$key]['open_time'] ?? '09:00')[1] ?? 0)],
                    'closeTime' => ['hours' => (int) explode(':', $hours[$key]['close_time'] ?? '17:00')[0], 'minutes' => (int) (explode(':', $hours[$key]['close_time'] ?? '17:00')[1] ?? 0)],
                ];
            }
        }

        return ['periods' => $periods];
    }
}
