<?php

namespace Database\Factories\Domain\AI\Models;

use App\Domain\AI\Models\AiJob;
use App\Domain\Content\Models\BnccSkill;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AiJob> */
class AiJobFactory extends Factory
{
    protected $model = AiJob::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['generate_questions', 'explain_error', 'validate_batch']),
            'skill_id' => BnccSkill::factory(),
            'status' => 'pending',
            'model' => 'claude-sonnet-4-6',
            'prompt_tokens' => 0,
            'result_tokens' => 0,
            'questions_generated' => 0,
            'started_at' => null,
            'finished_at' => null,
        ];
    }

    public function done(): static
    {
        return $this->state([
            'status' => 'done',
            'questions_generated' => fake()->numberBetween(1, 10),
            'prompt_tokens' => fake()->numberBetween(100, 1000),
            'result_tokens' => fake()->numberBetween(100, 500),
            'started_at' => now()->subMinutes(2),
            'finished_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => 'failed',
            'error' => fake()->sentence(),
            'started_at' => now()->subMinutes(1),
        ]);
    }
}
