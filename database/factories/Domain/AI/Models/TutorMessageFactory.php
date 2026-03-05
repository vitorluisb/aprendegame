<?php

namespace Database\Factories\Domain\AI\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\AI\Models\TutorMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TutorMessage> */
class TutorMessageFactory extends Factory
{
    protected $model = TutorMessage::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'role' => 'student',
            'content' => fake()->sentence(),
            'blocked' => false,
            'blocked_reason' => null,
            'prompt_tokens' => 0,
            'result_tokens' => 0,
        ];
    }

    public function fromTutor(): static
    {
        return $this->state([
            'role' => 'tutor',
            'prompt_tokens' => fake()->numberBetween(100, 500),
            'result_tokens' => fake()->numberBetween(50, 300),
        ]);
    }

    public function blocked(): static
    {
        return $this->state([
            'blocked' => true,
            'blocked_reason' => 'Tópico não permitido: violência',
        ]);
    }
}
