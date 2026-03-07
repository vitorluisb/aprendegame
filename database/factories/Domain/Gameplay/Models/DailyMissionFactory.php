<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Gameplay\Models\DailyMission;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DailyMission> */
class DailyMissionFactory extends Factory
{
    protected $model = DailyMission::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['study', 'streak', 'xp', 'perfect_lesson']),
            'target_value' => fake()->numberBetween(1, 10),
            'xp_reward' => fake()->randomElement([5, 10, 15]),
            'active' => true,
        ];
    }
}
