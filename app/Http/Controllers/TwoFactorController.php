<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;

/**
 * 2FA flow using pragmarx/google2fa.
 *
 *  - show()    : settings screen (status + setup form when enabling).
 *  - enable()  : generates a fresh secret + QR code, stores secret (not yet enabled).
 *  - confirm() : user types a TOTP code; on success flips two_factor_enabled=true and
 *                generates 8 one-time recovery codes.
 *  - disable() : password-protected disable.
 *  - challenge()/verify() : login challenge after primary auth (used by middleware).
 */
class TwoFactorController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        return Inertia::render('settings/TwoFactor', [
            'enabled'        => (bool) $user->two_factor_enabled,
            'has_secret'     => filled($user->two_factor_secret),
            'recovery_codes' => $user->two_factor_enabled ? ($user->two_factor_recovery_codes ?? []) : [],
        ]);
    }

    public function enable(Request $request)
    {
        $g2fa   = new Google2FA;
        $secret = $g2fa->generateSecretKey();
        $user   = $request->user();
        $user->forceFill([
            'two_factor_secret'  => $secret,
            'two_factor_enabled' => false,
        ])->save();

        $otpUrl = $g2fa->getQRCodeUrl(config('app.name', 'CRM'), $user->email, $secret);

        return back()->with('two_factor_setup', [
            'secret'  => $secret,
            'otp_url' => $otpUrl,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $user = $request->user();
        $g2fa = new Google2FA;

        if (! $user->two_factor_secret || ! $g2fa->verifyKey($user->two_factor_secret, $request->input('code'))) {
            throw ValidationException::withMessages(['code' => 'Código inválido.']);
        }

        $codes = collect(range(1, 8))->map(fn () => Str::random(10))->all();
        $user->forceFill([
            'two_factor_enabled'        => true,
            'two_factor_recovery_codes' => $codes,
        ])->save();

        $request->session()->put('2fa_passed', true);

        return back()->with('recovery_codes', $codes);
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $request->user()->forceFill([
            'two_factor_secret'         => null,
            'two_factor_enabled'        => false,
            'two_factor_recovery_codes' => null,
        ])->save();

        return back()->with('success', '2FA desativado.');
    }

    public function challenge()
    {
        return Inertia::render('auth/TwoFactorChallenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);
        $user  = $request->user();
        $code  = (string) $request->input('code');
        $g2fa  = new Google2FA;

        // Try TOTP first.
        if ($user->two_factor_secret && $g2fa->verifyKey($user->two_factor_secret, $code)) {
            $request->session()->put('2fa_passed', true);
            return redirect()->intended(route('dashboard'));
        }

        // Fallback: one-time recovery code.
        $codes = $user->two_factor_recovery_codes ?? [];
        if (in_array($code, $codes, true)) {
            $remaining = array_values(array_filter($codes, fn ($c) => $c !== $code));
            $user->forceFill(['two_factor_recovery_codes' => $remaining])->save();
            $request->session()->put('2fa_passed', true);
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages(['code' => 'Código inválido.']);
    }
}
