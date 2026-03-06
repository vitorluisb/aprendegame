<?php

namespace App\Http\Responses;

use App\Domain\Accounts\Enums\UserRole;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user?->role === UserRole::Guardian->value) {
            return redirect()->route('guardian.dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }
}
