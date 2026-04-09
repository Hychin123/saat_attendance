<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Skip 2FA check for these routes
        $excludedRoutes = [
            '2fa/challenge',
            '2fa/verify',
            '2fa/logout',
            'admin/login',
            'admin/two-factor-authentication', // Allow accessing the 2FA setup page
        ];

        // Check if the current path matches any excluded route
        foreach ($excludedRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // If user has 2FA enabled but hasn't verified this session
        if ($user && $user->google2fa_enabled && !session('2fa_verified')) {
            // Don't redirect if already on the challenge page
            if (!$request->is('2fa/challenge')) {
                // Store the intended URL
                session(['url.intended' => $request->url()]);
                return redirect()->route('2fa.challenge');
            }
        }

        return $next($request);
    }
}
