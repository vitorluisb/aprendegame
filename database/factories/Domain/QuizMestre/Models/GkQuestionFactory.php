<?php

namespace Database\Factories\Domain\QuizMestre\Models;

use App\Domain\QuizMestre\Models\GkCategory;
use App\Domain\QuizMestre\Models\GkQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GkQuestion> */
class GkQuestionFactory extends Factory
{
    protected $model = GkQuestion::class;

    public function definition(): array
    {
        $category = GkCategory::query()->inRandomOrder()->first() ?? GkCategory::factory()->create();

        return [
            'category_id' => $category->id,
            'question_text' => fake()->sentence(10).'?',
            'option_a' => fake()->sentence(4),
            'option_b' => fake()->sentence(4),
            'option_c' => fake()->sentence(4),
            'option_d' => fake()->sentence(4),
            'correct_option' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'explanation' => fake()->sentence(),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'age_group' => fake()->randomElement(['8-10', '11-14', '15-18']),
            'is_active' => true,
            'source_reference' => null,
            'metadata_json' => null,
        ];
    }
}
