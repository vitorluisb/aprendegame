<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    protected function passwordRules(): array
    {
        $passwordRule = Password::min(12)->mixedCase()->numbers()->symbols();

        if (! app()->environment('testing')) {
            $passwordRule = $passwordRule->uncompromised();
        }

        return [
            'required',
            'string',
            $passwordRule,
            'confirmed',
        ];
    }
}
