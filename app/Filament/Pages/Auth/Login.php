<?php

namespace App\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;

class Login extends BaseLogin
{
    use WithRateLimiting;

    public function authenticate(): ?LoginResponse
    {
        try {
            // Rate limit: 5 attempts per minute per IP address
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.email' => __('Too many login attempts. Please try again in :seconds seconds.', [
                    'seconds' => $exception->secondsUntilAvailable,
                ]),
            ]);
        }

        $response = parent::authenticate();
        
        // After successful authentication, clear any previous 2FA verification
        session()->forget('2fa_verified');
        
        return $response;
    }

    protected function getRateLimitKey($method, $component = null): string
    {
        return 'login:' . request()->ip();
    }
}
