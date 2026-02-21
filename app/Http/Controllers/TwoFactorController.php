<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function showChallenge()
    {
        if (!Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        $user = Auth::user();
        
        if (!$user->google2fa_enabled) {
            return redirect()->intended('/admin');
        }

        if (session('2fa_verified')) {
            return redirect()->intended('/admin');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = Auth::user();

        if (!$user || !$user->google2fa_enabled) {
            return redirect()->route('filament.admin.auth.login');
        }

        $google2fa = new Google2FA();
        $secret = decrypt($user->google2fa_secret);
        
        $valid = $google2fa->verifyKey($secret, $request->code, 2); // 2 = tolerance window

        if ($valid) {
            session(['2fa_verified' => true]);
            return redirect()->intended('/admin');
        }

        return back()->withErrors([
            'code' => 'The verification code is invalid.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        session()->forget('2fa_verified');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('filament.admin.auth.login');
    }
}
