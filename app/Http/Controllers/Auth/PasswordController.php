<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Send OTP for password update.
     */
    public function sendOtp(Request $request)
    {
        $user = $request->user();
        $isDefaultEmail = $user->email === $user->dni || !str_contains($user->email, '@') || str_ends_with($user->email, '@sistema.edu') || str_ends_with($user->email, '@sistema.edu.pe');
        if (!$user->hasVerifiedEmail() || $isDefaultEmail) {
            return response()->json(['error' => 'Correo no verificado o inválido.'], 403);
        }

        $code = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
        \Illuminate\Support\Facades\Cache::put('password_otp_' . $user->id, $code, now()->addMinutes(10));

        \Illuminate\Support\Facades\Mail::raw("Tu código de verificación para cambiar tu contraseña es: {$code}\n\nEste código es válido por 10 minutos.", function ($msg) use ($user) {
            $msg->to($user->email)->subject('Código de verificación - Cambio de Contraseña');
        });

        return response()->json(['message' => 'Código enviado correctamente.']);
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isDefaultEmail = $user->email === $user->dni || !str_contains($user->email, '@') || str_ends_with($user->email, '@sistema.edu') || str_ends_with($user->email, '@sistema.edu.pe');
        $hasVerifiedEmail = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && $user->hasVerifiedEmail() && !$isDefaultEmail;

        if ($hasVerifiedEmail) {
            $request->validateWithBag('updatePassword', [
                'otp_code' => ['required', 'string'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);

            $cachedCode = \Illuminate\Support\Facades\Cache::get('password_otp_' . $user->id);
            if (!$cachedCode || $cachedCode !== $request->otp_code) {
                return back()->withErrors(['otp_code' => 'El código de verificación es incorrecto o ha expirado.'], 'updatePassword');
            }
            \Illuminate\Support\Facades\Cache::forget('password_otp_' . $user->id);
        } else {
            $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        if (!$isDefaultEmail) {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordChangedNotification($user));
        }

        return back()->with('status', 'password-updated');
    }
}
