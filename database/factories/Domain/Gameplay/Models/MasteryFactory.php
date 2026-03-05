<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Gameplay\Models\Mastery;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Mastery> */
class MasteryFactory extends Factory
{
    protected $model = Mastery::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'skill_id' => BnccSkill::factory(),
            'mastery_score' => fake()->numberBetween(0, 80),
            'interval_days' => fake()->randomElement([1, 3, 7, 14, 30]),
            'consecutive_correct' => fake()->numberBetween(0, 5),
            'last_seen_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'next_review_at' => now()->addDays(fake()->numberBetween(1, 30)),
        ];
    }

    public function due(): static
    {
        return $this->state([
            'next_review_at' => now()->subDay(),
            'mastery_score' => fake()->numberBetween(0, 80),
        ]);
    }

    public function mastered(): static
    {
        return $this->state([
            'mastery_score' => 90,
            'next_review_at' => now()->subDay(),
        ]);
    }
}
