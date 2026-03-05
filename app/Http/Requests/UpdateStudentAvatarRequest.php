<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentAvatarRequest extends FormRequest
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
            'avatar' => [
                'required',
                'file',
                'max:5120',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/avif,image/heic,image/heif',
            ],
        ];
    }
}
