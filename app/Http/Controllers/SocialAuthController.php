<?php

namespace App\Http\Controllers;

use App\Domain\Accounts\Services\AuditLog;
use App\Domain\Accounts\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $socialUser = Socialite::driver('google')->user();

        $user = User::query()->updateOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'name' => $socialUser->getName(),
                'password' => Hash::make(Str::random(40)),
                'role' => UserRole::Student->value,
                'provider' => 'google',
                'provider_id' => $socialUser->getId(),
                'avatar_url' => $socialUser->getAvatar(),
                'email_verified_at' => now(),
            ],
        );

        if ($user->shouldHaveStudentProfile()) {
            $user->ensureStudentProfile();
        }

        Auth::login($user);
        AuditLog::record($user, 'login', ['provider' => 'google']);

        return redirect()->intended('/dashboard');
    }
}
