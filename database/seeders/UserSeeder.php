<?php

namespace Database\Seeders;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\Accounts\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::query()->get();

        foreach ($schools as $school) {
            $this->seedUsersForSchool($school->id);
        }

        User::query()->updateOrCreate(
            ['email' => 'admin@aprende.test'],
            [
                'school_id' => null,
                'role' => UserRole::SuperAdmin->value,
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'super@platform.com'],
            [
                'school_id' => null,
                'role' => UserRole::SuperAdmin->value,
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedUsersForSchool(int $schoolId): void
    {
        $this->ensureRoleCount($schoolId, UserRole::Teacher->value, 3);
        $this->ensureRoleCount($schoolId, UserRole::Student->value, 5);

        User::query()->updateOrCreate(
            ['email' => 'admin-'.$schoolId.'@escola.com'],
            [
                'school_id' => $schoolId,
                'role' => UserRole::SchoolAdmin->value,
                'name' => 'Admin Escola '.$schoolId,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }

    private function ensureRoleCount(int $schoolId, string $role, int $expectedCount): void
    {
        $currentCount = User::query()
            ->where('school_id', $schoolId)
            ->where('role', $role)
            ->count();

        $missingCount = $expectedCount - $currentCount;

        if ($missingCount <= 0) {
            return;
        }

        User::factory()->count($missingCount)->create([
            'school_id' => $schoolId,
            'role' => $role,
        ]);
    }
}
