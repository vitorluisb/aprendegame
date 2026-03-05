<?php

namespace Database\Factories\Domain\Content\Models;

use App\Domain\Content\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Subject> */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Matemática', 'Português', 'Ciências', 'História',
            'Geografia', 'Inglês', 'Artes', 'Educação Física',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => fake()->randomElement(['📐', '📖', '🔬', '🏛️', '🌍', '🌎', '🎨', '⚽']),
            'color' => fake()->hexColor(),
        ];
    }
}
