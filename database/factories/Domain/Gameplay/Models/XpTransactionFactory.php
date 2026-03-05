<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\XpTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<XpTransaction> */
class XpTransactionFactory extends Factory
{
    protected $model = XpTransaction::class;

    public function definition(): array
    {
        $student = Student::inRandomOrder()->first() ?? Student::factory()->create();

        return [
            'student_id' => $student->id,
            'amount' => fake()->numberBetween(5, 35),
            'reason' => fake()->randomElement(['lesson', 'streak_bonus', 'challenge', 'badge']),
            'reference_type' => 'LessonRun',
            'reference_id' => fake()->numberBetween(1, 100),
        ];
    }
}
