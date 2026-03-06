<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreTutorMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->role === 'student'
            || $user->studentProfile()->withoutGlobalScopes()->exists();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'message.required' => 'Digite uma mensagem para enviar ao tutor.',
            'message.min' => 'A mensagem precisa ter ao menos 2 caracteres.',
            'message.max' => 'A mensagem pode ter no máximo 2000 caracteres.',
        ];
    }
}
