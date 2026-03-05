<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LessonRun> */
class LessonRunFactory extends Factory
{
    protected $model = LessonRun::class;

    public function definition(): array
    {
        $student = Student::inRandomOrder()->first() ?? Student::factory()->create();
        $lesson = Lesson::inRandomOrder()->first() ?? Lesson::factory()->create();

        return [
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
            'started_at' => now(),
            'finished_at' => null,
            'score' => 0,
            'xp_earned' => 0,
            'correct_count' => 0,
            'total_count' => 0,
        ];
    }

    public function finished(int $score = 80): static
    {
        return $this->state([
            'finished_at' => now(),
            'score' => $score,
        ]);
    }
}
