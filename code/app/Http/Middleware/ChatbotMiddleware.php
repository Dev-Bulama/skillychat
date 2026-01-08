<?php

namespace App\Http\Middleware;

use App\Models\Chatbot;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ChatbotMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $chatbotId = $request->input('chatbot_id') ?? $request->route('chatbotId');

        if (!$chatbotId) {
            return response()->json([
                'success' => false,
                'message' => 'Chatbot ID is required.',
            ], 400);
        }

        $chatbot = $this->getCachedChatbot($chatbotId);

        if (!$chatbot) {
            return response()->json([
                'success' => false,
                'message' => 'Chatbot not found.',
            ], 404);
        }

        if (!$chatbot->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Chatbot is currently inactive.',
            ], 403);
        }

        if (in_array('domain', $guards)) {
            if (!$this->validateDomain($request, $chatbot)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Domain not authorized for this chatbot.',
                ], 403);
            }
        }

        if (in_array('rate_limit', $guards)) {
            if (!$this->checkRateLimit($request, $chatbot)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Please try again later.',
                ], 429);
            }
        }

        if (in_array('subscription', $guards)) {
            if (!$this->checkSubscription($chatbot)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chatbot subscription has expired.',
                ], 403);
            }
        }

        $request->merge(['chatbot' => $chatbot]);

        return $next($request);
    }

    /**
     * Get cached chatbot
     *
     * @param string $chatbotId
     * @return Chatbot|null
     */
    protected function getCachedChatbot(string $chatbotId): ?Chatbot
    {
        return Cache::remember("chatbot:{$chatbotId}", 600, function () use ($chatbotId) {
            return Chatbot::where('uid', $chatbotId)
                ->with('user')
                ->first();
        });
    }

    /**
     * Validate domain authorization
     *
     * @param Request $request
     * @param Chatbot $chatbot
     * @return bool
     */
    protected function validateDomain(Request $request, Chatbot $chatbot): bool
    {
        if (empty($chatbot->domain)) {
            return true;
        }

        $referer = $request->header('referer');
        $origin = $request->header('origin');

        if (!$referer && !$origin) {
            return false;
        }

        $requestDomain = parse_url($referer ?? $origin, PHP_URL_HOST);
        $allowedDomain = parse_url($chatbot->domain, PHP_URL_HOST) ?? $chatbot->domain;

        if (!$requestDomain) {
            return false;
        }

        $allowedDomainPattern = str_replace('.', '\.', $allowedDomain);
        $allowedDomainPattern = '/^(.+\.)?' . $allowedDomainPattern . '$/i';

        return (bool) preg_match($allowedDomainPattern, $requestDomain);
    }

    /**
     * Check rate limit for chatbot requests
     *
     * @param Request $request
     * @param Chatbot $chatbot
     * @return bool
     */
    protected function checkRateLimit(Request $request, Chatbot $chatbot): bool
    {
        $visitorId = $request->input('visitor_id');
        $ip = $request->ip();

        $key = "chatbot_rate:{$chatbot->id}:{$visitorId}:{$ip}";

        $attempts = Cache::get($key, 0);

        $maxAttempts = 30;
        $decayMinutes = 1;

        if ($attempts >= $maxAttempts) {
            return false;
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        return true;
    }

    /**
     * Check if chatbot owner has active subscription
     *
     * @param Chatbot $chatbot
     * @return bool
     */
    protected function checkSubscription(Chatbot $chatbot): bool
    {
        if (!$chatbot->user) {
            return false;
        }

        $subscription = $chatbot->user->runningSubscription;

        if (!$subscription) {
            return false;
        }

        if ($subscription->expired_date && now()->greaterThan($subscription->expired_date)) {
            return false;
        }

        return true;
    }
}
