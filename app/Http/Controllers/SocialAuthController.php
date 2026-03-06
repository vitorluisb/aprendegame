<?php

namespace App\Http\Controllers;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\Accounts\Services\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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

        $existingUser = User::query()->where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            $existingUser->update([
                'provider' => 'google',
                'provider_id' => $socialUser->getId(),
                'avatar_url' => $existingUser->avatar_url ?? $socialUser->getAvatar(),
            ]);

            Auth::login($existingUser);
            AuditLog::record($existingUser, 'login', ['provider' => 'google']);

            return $existingUser->role === UserRole::Guardian->value
                ? redirect()->route('guardian.dashboard')
                : redirect()->intended('/dashboard');
        }

        // Novo usuário: armazenar dados na sessão e perguntar se é aluno ou responsável
        session()->put('google_pending_user', [
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'provider_id' => $socialUser->getId(),
            'avatar_url' => $socialUser->getAvatar(),
        ]);

        return redirect()->route('select-role');
    }
}
