<?php

namespace Database\Factories\Domain\QuizMestre\Models;

use App\Domain\QuizMestre\Models\GkCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GkCategory> */
class GkCategoryFactory extends Factory
{
    protected $model = GkCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['book-open', 'light-bulb', 'academic-cap']),
        ];
    }
}
