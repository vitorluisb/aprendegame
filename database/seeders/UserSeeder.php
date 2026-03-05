<?php

namespace Database\Seeders;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\Accounts\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $schools = School::query()->get();

        foreach ($schools as $school) {
            User::factory()->count(3)->create([
                'school_id' => $school->id,
                'role' => UserRole::Teacher->value,
            ]);

            User::factory()->count(5)->create([
                'school_id' => $school->id,
                'role' => UserRole::Student->value,
            ]);

            User::factory()->create([
                'school_id' => $school->id,
                'role' => UserRole::SchoolAdmin->value,
                'email' => 'admin-'.$school->id.'@escola.com',
            ]);
        }

        User::factory()->create([
            'school_id' => null,
            'role' => UserRole::SuperAdmin->value,
            'name' => 'Administrador',
            'email' => 'admin@aprende.test',
        ]);

        User::factory()->create([
            'school_id' => null,
            'role' => UserRole::SuperAdmin->value,
            'email' => 'super@platform.com',
        ]);
    }
}
