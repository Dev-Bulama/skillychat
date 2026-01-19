<?php

namespace App\Http\Controllers\User\Auth;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SocialAuthController extends Controller
{
    public $oauthCreds ;
    public function __construct(){

        $this->oauthCreds = json_decode(site_settings('social_login_with'),true);
    }


    public function redirectToOauth(Request $request, string $service)
    {
        $this->setConfig($service);

        // Get OAuth method preference (default: PKCE for security)
        $credential = Arr::get($this->oauthCreds, $service."_oauth", []);
        $oauthMethod = Arr::get($credential, 'oauth_method', 'pkce');

        $driver = Socialite::driver($service);

        // Apply PKCE if configured (more secure)
        if ($oauthMethod === 'pkce') {
            // For providers that support PKCE
            try {
                // Generate PKCE code verifier and challenge
                $codeVerifier = $this->generateCodeVerifier();
                $codeChallenge = $this->generateCodeChallenge($codeVerifier);

                // Store code verifier in session for callback
                session(['oauth_code_verifier' => $codeVerifier]);

                // Add PKCE parameters if the provider supports setScopes
                if (method_exists($driver, 'setScopes')) {
                    $driver = $driver->with([
                        'code_challenge' => $codeChallenge,
                        'code_challenge_method' => 'S256'
                    ]);
                }
            } catch (\Exception $e) {
                // Fall back to plain OAuth if PKCE fails
                \Log::warning("PKCE setup failed for {$service}, using plain OAuth: " . $e->getMessage());
            }
        }

        return $driver->redirect();
    }


    /**
     * Set configuration
     *
     * @param string $service
     * @return void
     */
    public function setConfig(string $service) :void{

        $credential = Arr::get($this->oauthCreds, $service."_oauth", []);

        // Use custom callback URL if configured, otherwise use default
        $callbackUrl = Arr::get($credential, 'callback_url', url('login/'.$service.'/callback'));
        $credential["redirect"] = $callbackUrl;

        // Remove settings-only fields that shouldn't be in Laravel config
        Arr::forget($credential, ['status', 'oauth_method', 'callback_url']);

        Config::set('services.'.$service, $credential);
    }

    /**
     * Generate PKCE code verifier
     *
     * @return string
     */
    private function generateCodeVerifier(): string
    {
        $length = 128;
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';
        $verifier = '';

        for ($i = 0; $i < $length; $i++) {
            $verifier .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $verifier;
    }

    /**
     * Generate PKCE code challenge from verifier
     *
     * @param string $verifier
     * @return string
     */
    private function generateCodeChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    /**
     * handle auth call back
     *
     * @param string $service
     * @return RedirectResponse
     */
    public function handleOauthCallback(string $service) : \Illuminate\Http\RedirectResponse
    {
        $this->setConfig($service);

        try {
            $driver = Socialite::driver($service);

            // Check if PKCE was used and add code_verifier if present
            $credential = Arr::get($this->oauthCreds, $service."_oauth", []);
            $oauthMethod = Arr::get($credential, 'oauth_method', 'pkce');

            if ($oauthMethod === 'pkce' && session('oauth_code_verifier')) {
                // Add code_verifier for PKCE flow
                try {
                    $driver = $driver->with([
                        'code_verifier' => session('oauth_code_verifier')
                    ]);
                } catch (\Exception $e) {
                    \Log::warning("PKCE verification failed for {$service}: " . $e->getMessage());
                }
                // Clear the code verifier from session
                session()->forget('oauth_code_verifier');
            }

            $userOauth = $driver->stateless()->user();

        } catch (\Exception $e) {
            \Log::error("OAuth callback error for {$service}: " . $e->getMessage());
            return back()->with('error', translate('Setup Your Social Credential!! Then Try Again'));
        }

        $user = User::where('email', $userOauth->email)->first();
        if (!$user) {
            $user                    = new User();
            $user->name              = Arr::get($userOauth->user, "name", null);
            $user->email             = $userOauth->email;
            $user->o_auth_id         = Arr::get($userOauth->user, "id", null);
            $user->email_verified_at = Carbon::now();
            $user->save();
        }

        Auth::guard('web')->login($user);
        return redirect()->route('user.home')->with(response_status("Login Success"));
    }
}
