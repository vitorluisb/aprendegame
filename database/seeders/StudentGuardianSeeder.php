<?php

namespace Database\Seeders;

use App\Domain\Accounts\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentGuardianSeeder extends Seeder
{
    public function run(): void
    {
        $guardians = User::factory()->count(10)->create(['role' => 'guardian']);
        $students = Student::query()->take(10)->get();

        foreach ($students as $index => $student) {
            $guardian = $guardians[$index % $guardians->count()];

            $guardian->guardiansOf()->syncWithoutDetaching([
                $student->id => [
                    'relationship' => 'parent',
                    'consent_given' => true,
                    'consent_given_at' => now(),
                ],
            ]);
        }
    }
}
