<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresSubscription
{
    /**
     * Block access to features that require an active subscription.
     * Redirects to the plans page with a clear error message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth_user('web');

        if (!$user) {
            return redirect()->route('auth.login');
        }

        $subscription = $user->runningSubscription;

        $isActive = $subscription
            && $subscription->status == \App\Enums\SubscriptionStatus::RUNNING->value
            && (
                is_null($subscription->expired_at)
                || now()->lte(\Carbon\Carbon::parse($subscription->expired_at)->endOfDay())
            );

        if (!$isActive) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => translate('An active subscription is required to use this feature.'),
                ], 403);
            }

            return redirect()->route('user.subscription.required')
                ->with('warning', translate('You need an active subscription to access this feature. Choose a plan to continue.'));
        }

        return $next($request);
    }
}
