<?php

namespace Database\Factories\Domain\Content\Models;

use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Path> */
class PathFactory extends Factory
{
    protected $model = Path::class;

    public function definition(): array
    {
        $grade = Grade::factory()->create();
        $subject = Subject::factory()->create();

        return [
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'title' => "{$grade->name} — {$subject->name}",
            'published' => false,
            'path_type' => 'regular',
        ];
    }

    public function published(): static
    {
        return $this->state(['published' => true]);
    }

    public function enem(): static
    {
        return $this->state(['path_type' => 'enem']);
    }
}
