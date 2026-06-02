<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use PragmaRX\Google2FA\Google2FA;

/**
 * Unified profile screen: identity + password + 2FA management
 * (modelled after the Fortify-style layout the user requested).
 */
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $g2fa = new Google2FA;

        $qrSvg  = null;
        $otpUrl = null;
        if ($user->two_factor_secret) {
            $otpUrl = $g2fa->getQRCodeUrl(config('app.name', 'CRM'), $user->email, $user->two_factor_secret);
            $qrSvg  = $this->renderQr($otpUrl);
        }

        return Inertia::render('profile/Show', [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'two_factor' => [
                'enabled'        => (bool) $user->two_factor_enabled,
                'has_secret'     => filled($user->two_factor_secret),
                'recovery_codes' => $user->two_factor_enabled ? ($user->two_factor_recovery_codes ?? []) : [],
                'qr_svg'         => $qrSvg,
                'otp_url'        => $otpUrl,
                'secret'         => $user->two_factor_secret,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
        ]);
        $user->update($data);
        return back()->with('success', 'Perfil atualizado.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'  => ['required', 'current_password'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->forceFill(['password' => Hash::make($request->input('password'))])->save();

        return back()->with('success', 'Password alterada.');
    }

    /**
     * Generate (or regenerate) a 2FA secret. Does NOT enable 2FA — user must
     * confirm with a TOTP code through confirmTwoFactor().
     */
    public function enableTwoFactor(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $g2fa = new Google2FA;
        $request->user()->forceFill([
            'two_factor_secret'  => $g2fa->generateSecretKey(),
            'two_factor_enabled' => false,
        ])->save();

        return back()->with('success', '2FA pronta para confirmação. Leia o QR code e introduza o código.');
    }

    public function confirmTwoFactor(Request $request)
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

        return back()->with('success', '2FA ativada.');
    }

    public function disableTwoFactor(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $request->user()->forceFill([
            'two_factor_secret'         => null,
            'two_factor_enabled'        => false,
            'two_factor_recovery_codes' => null,
        ])->save();

        return back()->with('success', '2FA desativada.');
    }

    private function renderQr(string $data): string
    {
        $renderer = new ImageRenderer(new RendererStyle(192, 1), new SvgImageBackEnd);
        return (new Writer($renderer))->writeString($data);
    }
}
