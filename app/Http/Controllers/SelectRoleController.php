<?php

namespace App\Http\Controllers;

use App\Domain\Accounts\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SelectRoleController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        // Novo usuário via Google aguardando seleção de papel
        if (session()->has('google_pending_user')) {
            return Inertia::render('Auth/SelectRole', [
                'pending_name' => session('google_pending_user.name'),
            ]);
        }

        // Usuário já logado aguardando seleção de papel
        if (auth()->check() && session()->has('needs_role_selection')) {
            return Inertia::render('Auth/SelectRole');
        }

        // Já tem papel definido: redirecionar
        if (auth()->check()) {
            $role = auth()->user()?->role;

            return redirect($role === UserRole::Guardian->value ? '/responsavel' : '/dashboard');
        }

        return redirect()->route('login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:student,guardian'],
        ]);

        $role = $request->string('role')->toString();

        // Caso 1: novo usuário via Google (sem conta ainda)
        if (session()->has('google_pending_user')) {
            $pending = session('google_pending_user');

            $user = User::create([
                'name' => $pending['name'],
                'email' => $pending['email'],
                'password' => Hash::make(Str::random(40)),
                'role' => $role,
                'provider' => 'google',
                'provider_id' => $pending['provider_id'],
                'avatar_url' => $pending['avatar_url'],
                'email_verified_at' => now(),
            ]);

            session()->forget('google_pending_user');

            if ($role === UserRole::Student->value) {
                $user->ensureStudentProfile();
            }

            Auth::login($user);

            return redirect($role === UserRole::Guardian->value ? '/responsavel' : '/dashboard');
        }

        // Caso 2: usuário já logado que precisa escolher papel
        /** @var User $user */
        $user = auth()->user();
        $user->update(['role' => $role]);
        session()->forget('needs_role_selection');

        if ($role === UserRole::Student->value) {
            $user->ensureStudentProfile();
        }

        return redirect($role === UserRole::Guardian->value ? '/responsavel' : '/dashboard');
    }
}
