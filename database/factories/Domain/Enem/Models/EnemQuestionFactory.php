<?php

namespace Database\Factories\Domain\Enem\Models;

use App\Domain\Enem\Models\EnemQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EnemQuestion> */
class EnemQuestionFactory extends Factory
{
    protected $model = EnemQuestion::class;

    public function definition(): array
    {
        return [
            'area' => fake()->randomElement(['linguagens', 'humanas', 'natureza', 'matematica']),
            'subject' => fake()->randomElement(['Língua Portuguesa', 'História', 'Biologia', 'Matemática']),
            'enem_code' => null,
            'context_text' => fake()->paragraphs(2, true),
            'question_text' => fake()->sentence(18).'?',
            'option_a' => fake()->sentence(8),
            'option_b' => fake()->sentence(8),
            'option_c' => fake()->sentence(8),
            'option_d' => fake()->sentence(8),
            'option_e' => fake()->sentence(8),
            'correct_option' => fake()->randomElement(['A', 'B', 'C', 'D', 'E']),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'year_reference' => fake()->numberBetween(2018, 2025),
            'explanation' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'reviewed', 'approved']),
            'is_active' => true,
        ];
    }
}
