<?php

namespace Database\Seeders;

use App\Domain\Accounts\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        User::query()
            ->where('role', 'student')
            ->each(function (User $user): void {
                Student::factory()->create([
                    'user_id' => $user->id,
                    'school_id' => $user->school_id,
                    'name' => $user->name,
                ]);
            });
    }
}
