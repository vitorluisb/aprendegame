<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Attempt;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Attempt> */
class AttemptFactory extends Factory
{
    protected $model = Attempt::class;

    public function definition(): array
    {
        $student = Student::inRandomOrder()->first() ?? Student::factory()->create();
        $question = Question::inRandomOrder()->first() ?? Question::factory()->create();
        $run = LessonRun::inRandomOrder()->first() ?? LessonRun::factory()->create();

        return [
            'student_id' => $student->id,
            'question_id' => $question->id,
            'run_id' => $run->id,
            'correct' => fake()->boolean(70),
            'time_ms' => fake()->numberBetween(1000, 30000),
            'given_answer' => 'A',
        ];
    }

    public function correct(): static
    {
        return $this->state(['correct' => true]);
    }

    public function wrong(): static
    {
        return $this->state(['correct' => false]);
    }
}
