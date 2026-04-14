<?php

namespace App\Services\Google;

use App\Models\GoogleAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBusinessService
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    private const OAUTH_AUTHORIZE = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const OAUTH_TOKEN     = 'https://oauth2.googleapis.com/token';
    private const USERINFO        = 'https://www.googleapis.com/oauth2/v3/userinfo';
    private const ACCOUNTS_LIST   = 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts';
    private const LOCATIONS_BASE  = 'https://mybusinessbusinessinformation.googleapis.com/v1';
    private const REVIEWS_BASE    = 'https://mybusiness.googleapis.com/v4';
    private const POSTS_BASE      = 'https://mybusiness.googleapis.com/v4';
    private const INSIGHTS_BASE   = 'https://mybusiness.googleapis.com/v4';
    private const SCOPES          = 'https://www.googleapis.com/auth/business.manage';

    public function __construct()
    {
        $this->clientId     = site_settings('google_oauth_client_id', '');
        $this->clientSecret = site_settings('google_oauth_client_secret', '');
        $this->redirectUri  = site_settings('google_oauth_redirect_uri', url('/user/google-business/callback'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // OAuth helpers
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Build the Google OAuth authorisation URL.
     */
    public function getAuthUrl(string $userId): string
    {
        $params = http_build_query([
            'client_id'             => $this->clientId,
            'redirect_uri'          => $this->redirectUri,
            'response_type'         => 'code',
            'scope'                 => self::SCOPES,
            'access_type'           => 'offline',
            'prompt'                => 'consent',
            'state'                 => $userId,
        ]);

        return self::OAUTH_AUTHORIZE . '?' . $params;
    }

    /**
     * Exchange an authorisation code for tokens.
     *
     * @return array{success:bool, data:array, error:string}
     */
    public function exchangeCode(string $code): array
    {
        try {
            $response = Http::asForm()->post(self::OAUTH_TOKEN, [
                'code'          => $code,
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $this->redirectUri,
                'grant_type'    => 'authorization_code',
            ]);

            if ($response->failed()) {
                return [
                    'success' => false,
                    'data'    => [],
                    'error'   => 'Token exchange failed: ' . $response->body(),
                ];
            }

            return ['success' => true, 'data' => $response->json(), 'error' => ''];
        } catch (\Throwable $e) {
            Log::error('GoogleBusiness exchangeCode: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    /**
     * Refresh an expired access token and persist the new values.
     */
    public function refreshToken(GoogleAccount $account): bool
    {
        $refreshToken = $account->getPlainRefreshToken();

        if (empty($refreshToken)) {
            $account->update(['status' => 'expired']);
            return false;
        }

        try {
            $response = Http::asForm()->post(self::OAUTH_TOKEN, [
                'refresh_token' => $refreshToken,
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'refresh_token',
            ]);

            if ($response->failed()) {
                $account->update(['status' => 'expired']);
                return false;
            }

            $data = $response->json();

            $account->update([
                'access_token' => encrypt($data['access_token']),
                'token_expiry' => Carbon::now()->addSeconds($data['expires_in'] ?? 3600),
                'status'       => 'connected',
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('GoogleBusiness refreshToken: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Return a valid (possibly refreshed) access token string.
     */
    public function getAccessToken(GoogleAccount $account): string
    {
        if ($account->isExpired()) {
            $this->refreshToken($account);
            $account->refresh();
        }

        return $account->getPlainAccessToken();
    }

    // ────────────────────────────────────────────────────────────────────────
    // User info
    // ────────────────────────────────────────────────────────────────────────

    public function getUserInfo(string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)->get(self::USERINFO);

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'UserInfo request failed: ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json(), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Business Management API calls
    // ────────────────────────────────────────────────────────────────────────

    public function getAccounts(GoogleAccount $account): array
    {
        try {
            $token    = $this->getAccessToken($account);
            $response = Http::withToken($token)->get(self::ACCOUNTS_LIST);

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Accounts list failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json('accounts', []), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    public function getLocations(GoogleAccount $account, string $accountName): array
    {
        try {
            $token = $this->getAccessToken($account);
            $readMask = 'name,title,storefrontAddress,phoneNumbers,websiteUri,regularHours,profile,categories';

            $response = Http::withToken($token)
                ->get(self::LOCATIONS_BASE . "/{$accountName}/locations", ['readMask' => $readMask]);

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Locations list failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json('locations', []), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    public function getLocation(GoogleAccount $account, string $locationName): array
    {
        try {
            $token    = $this->getAccessToken($account);
            $readMask = 'name,title,storefrontAddress,phoneNumbers,websiteUri,regularHours,profile,categories';

            $response = Http::withToken($token)
                ->get(self::LOCATIONS_BASE . "/{$locationName}", ['readMask' => $readMask]);

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Get location failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json(), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    public function updateLocation(GoogleAccount $account, string $locationName, array $data): array
    {
        try {
            $token      = $this->getAccessToken($account);
            $updateMask = 'title,phoneNumbers,websiteUri,regularHours,profile';

            $response = Http::withToken($token)
                ->patch(self::LOCATIONS_BASE . "/{$locationName}?updateMask={$updateMask}", $data);

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Update location failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json(), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Reviews
    // ────────────────────────────────────────────────────────────────────────

    public function getReviews(GoogleAccount $account, string $locationName): array
    {
        try {
            $token    = $this->getAccessToken($account);
            $response = Http::withToken($token)
                ->get(self::REVIEWS_BASE . "/{$locationName}/reviews");

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Get reviews failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json('reviews', []), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    public function replyToReview(GoogleAccount $account, string $reviewName, string $reply): array
    {
        try {
            $token    = $this->getAccessToken($account);
            $response = Http::withToken($token)
                ->put(self::REVIEWS_BASE . "/{$reviewName}/reply", ['comment' => $reply]);

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Reply to review failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json(), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    public function deleteReviewReply(GoogleAccount $account, string $reviewName): array
    {
        try {
            $token    = $this->getAccessToken($account);
            $response = Http::withToken($token)
                ->delete(self::REVIEWS_BASE . "/{$reviewName}/reply");

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Delete review reply failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => [], 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Posts
    // ────────────────────────────────────────────────────────────────────────

    public function getPosts(GoogleAccount $account, string $locationName): array
    {
        try {
            $token    = $this->getAccessToken($account);
            $response = Http::withToken($token)
                ->get(self::POSTS_BASE . "/{$locationName}/localPosts");

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Get posts failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json('localPosts', []), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    public function createPost(GoogleAccount $account, string $locationName, array $data): array
    {
        try {
            $token    = $this->getAccessToken($account);
            $response = Http::withToken($token)
                ->post(self::POSTS_BASE . "/{$locationName}/localPosts", $data);

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Create post failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json(), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    public function deletePost(GoogleAccount $account, string $postName): array
    {
        try {
            $token    = $this->getAccessToken($account);
            $response = Http::withToken($token)
                ->delete(self::POSTS_BASE . "/{$postName}");

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Delete post failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => [], 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Insights
    // ────────────────────────────────────────────────────────────────────────

    public function getInsights(GoogleAccount $account, string $locationName, string $startDate, string $endDate): array
    {
        try {
            $token = $this->getAccessToken($account);

            $body = [
                'locationNames' => [$locationName],
                'basicRequest'  => [
                    'metricRequests' => [
                        ['metric' => 'ALL'],
                    ],
                    'timeRange' => [
                        'startTime' => $startDate,
                        'endTime'   => $endDate,
                    ],
                ],
            ];

            $response = Http::withToken($token)
                ->post(self::INSIGHTS_BASE . "/{$locationName}/reportInsights", $body);

            if ($response->failed()) {
                return ['success' => false, 'data' => [], 'error' => 'Get insights failed (' . $response->status() . '): ' . $response->body()];
            }

            return ['success' => true, 'data' => $response->json(), 'error' => ''];
        } catch (\Throwable $e) {
            return ['success' => false, 'data' => [], 'error' => $e->getMessage()];
        }
    }
}
