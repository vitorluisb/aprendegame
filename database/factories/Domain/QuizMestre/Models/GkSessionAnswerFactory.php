<?php

namespace Database\Factories\Domain\QuizMestre\Models;

use App\Domain\QuizMestre\Models\GkQuestion;
use App\Domain\QuizMestre\Models\GkSession;
use App\Domain\QuizMestre\Models\GkSessionAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GkSessionAnswer> */
class GkSessionAnswerFactory extends Factory
{
    protected $model = GkSessionAnswer::class;

    public function definition(): array
    {
        return [
            'session_id' => GkSession::factory(),
            'question_id' => GkQuestion::factory(),
            'selected_option' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'is_correct' => fake()->boolean(),
            'response_time_ms' => fake()->numberBetween(800, 15000),
        ];
    }
}
