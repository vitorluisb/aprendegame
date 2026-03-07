<?php

namespace Database\Seeders\Gameplay;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\GemTransaction;
use Illuminate\Database\Seeder;

class StudentNeuronsSeeder extends Seeder
{
    public function run(): void
    {
        Student::query()
            ->select('id')
            ->orderBy('id')
            ->each(function (Student $student): void {
                GemTransaction::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'source' => 'seed_neurons',
                    ],
                    [
                        'amount' => 500,
                        'created_at' => now(),
                    ]
                );
            });
    }
}
