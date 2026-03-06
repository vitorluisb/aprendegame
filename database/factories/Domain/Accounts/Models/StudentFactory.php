<?php

namespace Database\Factories\Domain\Accounts\Models;

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Student> */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'school_id' => School::factory(),
            'name' => fake()->name(),
            'birth_date' => fake()->date(),
            'avatar_url' => fake()->imageUrl(),
            'lives_current' => 5,
            'lives_max' => 5,
            'lives_refilled_at' => now(),
        ];
    }
}
