<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Forces users with 2FA enabled to complete the TOTP challenge once per session.
 * Apply AFTER `auth`. Skips if not authenticated, 2FA disabled, or session flag set.
 */
class EnsureTwoFactor
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->two_factor_enabled) {
            return $next($request);
        }
        if ($request->session()->get('2fa_passed')) {
            return $next($request);
        }
        // Allow the challenge endpoints + logout themselves through.
        $allowed = ['2fa.challenge', '2fa.verify', 'logout'];
        if (in_array($request->route()?->getName(), $allowed, true)) {
            return $next($request);
        }
        return redirect()->route('2fa.challenge');
    }
}
