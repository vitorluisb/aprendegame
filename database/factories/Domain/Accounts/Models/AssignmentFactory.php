<?php

namespace Database\Factories\Domain\Accounts\Models;

use App\Domain\Accounts\Models\Assignment;
use App\Domain\Accounts\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Assignment> */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'class_id' => SchoolClass::factory(),
            'teacher_id' => User::factory()->create(['role' => 'teacher'])->id,
            'type' => fake()->randomElement(['nodes', 'lesson', 'simulation']),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'due_at' => now()->addDays(7),
            'config' => null,
        ];
    }
}
