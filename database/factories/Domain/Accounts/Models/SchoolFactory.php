<?php

namespace Database\Factories\Domain\Accounts\Models;

use App\Domain\Accounts\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<School> */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 999),
            'plan' => fake()->randomElement(['basic', 'pro', 'enterprise']),
            'settings' => ['allow_student_login' => true],
            'active' => true,
        ];
    }
}
