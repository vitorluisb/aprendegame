<?php

namespace App\Domain\Accounts\Services;

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\SchoolMember;
use App\Mail\InviteMail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteService
{
    public function invite(string $email, School $school, string $role): string
    {
        $token = Str::random(64);

        Cache::put("invite:{$token}", [
            'email' => $email,
            'school_id' => $school->id,
            'role' => $role,
        ], now()->addHours(48));

        Mail::to($email)->send(new InviteMail($token, $school));

        return $token;
    }

    /**
     * @throws Exception
     */
    public function accept(string $token, User $user): void
    {
        $data = Cache::pull("invite:{$token}");

        if (! is_array($data) || ($data['email'] ?? null) !== $user->email) {
            throw new Exception('Convite inválido ou expirado');
        }

        SchoolMember::updateOrCreate(
            [
                'user_id' => $user->id,
                'school_id' => $data['school_id'],
            ],
            [
                'role' => $data['role'],
                'accepted_at' => now(),
            ],
        );
    }
}
