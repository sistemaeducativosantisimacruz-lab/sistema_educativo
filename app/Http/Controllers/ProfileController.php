<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
            
            // Send verification email if it's a real email address
            $email = $request->user()->email;
            $isDefault = $email === $request->user()->dni || !str_contains($email, '@') || str_ends_with($email, '@sistema.edu') || str_ends_with($email, '@sistema.edu.pe');
            
            if (!$isDefault && $request->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
                $request->user()->sendEmailVerificationNotification();
            }
        }

        $request->user()->save();

        if ($request->has('sexo') && $request->user()->estudiante) {
            $request->user()->estudiante->update(['sexo' => $request->sexo]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
