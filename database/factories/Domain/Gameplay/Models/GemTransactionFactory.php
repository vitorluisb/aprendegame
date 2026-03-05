<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\GemTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GemTransaction> */
class GemTransactionFactory extends Factory
{
    protected $model = GemTransaction::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'amount' => fake()->numberBetween(5, 100),
            'source' => fake()->randomElement(['lesson', 'bonus']),
            'created_at' => now(),
        ];
    }

    public function spend(): static
    {
        return $this->state([
            'amount' => -fake()->numberBetween(5, 100),
            'source' => 'purchase',
        ]);
    }
}
