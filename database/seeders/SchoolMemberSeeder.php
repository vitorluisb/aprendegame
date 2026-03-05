<?php

namespace Database\Seeders;

use App\Domain\Accounts\Models\SchoolMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolMemberSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->whereIn('role', ['teacher', 'school_admin'])
            ->whereNotNull('school_id')
            ->each(function (User $user): void {
                SchoolMember::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'school_id' => $user->school_id,
                    ],
                    [
                        'role' => $user->role,
                        'invited_at' => now()->subDay(),
                        'accepted_at' => now(),
                        'invited_by_email' => 'system@platform.com',
                    ],
                );
            });
    }
}
