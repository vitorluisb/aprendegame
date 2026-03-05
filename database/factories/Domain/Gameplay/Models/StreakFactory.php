<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Streak;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Streak> */
class StreakFactory extends Factory
{
    protected $model = Streak::class;

    public function definition(): array
    {
        $student = Student::factory()->create();
        $current = fake()->numberBetween(1, 30);

        return [
            'student_id' => $student->id,
            'current' => $current,
            'best' => fake()->numberBetween($current, $current + 10),
            'last_activity_date' => today(),
            'freeze_used_at' => null,
        ];
    }
}
