<?php

namespace App\Actions\Fortify;

use App\Domain\Accounts\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'role' => ['nullable', Rule::in([UserRole::Student->value, UserRole::Guardian->value])],
        ])->validate();

        $role = isset($input['role']) && $input['role'] === UserRole::Guardian->value
            ? UserRole::Guardian->value
            : UserRole::Student->value;

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => $role,
        ]);

        if ($role === UserRole::Student->value) {
            $user->ensureStudentProfile();
        }

        return $user;
    }
}
