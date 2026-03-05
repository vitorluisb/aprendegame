<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\LeagueSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeagueSnapshot> */
class LeagueSnapshotFactory extends Factory
{
    protected $model = LeagueSnapshot::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'school_id' => null,
            'class_id' => null,
            'league' => fake()->randomElement(['bronze', 'silver', 'gold', 'platinum']),
            'weekly_xp' => fake()->numberBetween(10, 500),
            'rank_position' => fake()->numberBetween(1, 20),
            'week' => now()->weekOfYear,
            'year' => now()->year,
        ];
    }
}
