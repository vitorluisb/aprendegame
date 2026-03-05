<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Gameplay\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Badge> */
class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['🏆', '⭐', '🔥', '💎', '🎯']),
            'condition_type' => fake()->randomElement(['streak', 'xp', 'lesson', 'perfect_score']),
            'condition_value' => fake()->numberBetween(1, 100),
            'active' => true,
        ];
    }
}
