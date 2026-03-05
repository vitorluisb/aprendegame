<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Question> */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        $skill = BnccSkill::inRandomOrder()->first() ?? $this->createSkill();
        $type = fake()->randomElement(['multiple_choice', 'true_false', 'fill_blank', 'order_steps', 'drag_drop', 'short_answer']);

        return [
            'skill_id' => $skill->id,
            'type' => $type,
            'difficulty' => fake()->numberBetween(1, 5),
            'prompt' => fake()->sentence(10).'?',
            'options' => $this->optionsForType($type),
            'correct_answer' => 'A',
            'explanation' => fake()->sentence(15),
            'status' => 'draft',
            'ai_generated' => false,
            'avg_time_ms' => 0,
            'error_rate' => 0,
        ];
    }

    public function multipleChoice(): static
    {
        return $this->state([
            'type' => 'multiple_choice',
            'options' => [
                ['key' => 'A', 'text' => fake()->sentence(5)],
                ['key' => 'B', 'text' => fake()->sentence(5)],
                ['key' => 'C', 'text' => fake()->sentence(5)],
                ['key' => 'D', 'text' => fake()->sentence(5)],
            ],
            'correct_answer' => 'A',
        ]);
    }

    public function trueFalse(): static
    {
        return $this->state([
            'type' => 'true_false',
            'options' => null,
            'correct_answer' => 'true',
        ]);
    }

    public function fillBlank(): static
    {
        return $this->state([
            'type' => 'fill_blank',
            'options' => null,
            'correct_answer' => fake()->word(),
        ]);
    }

    public function dragDrop(): static
    {
        return $this->state([
            'type' => 'drag_drop',
            'options' => null,
            'correct_answer' => json_encode(['item1' => 'alvo1', 'item2' => 'alvo2']),
        ]);
    }

    public function orderSteps(): static
    {
        return $this->state([
            'type' => 'order_steps',
            'options' => null,
            'correct_answer' => json_encode(['passo1', 'passo2', 'passo3']),
        ]);
    }

    public function published(): static
    {
        return $this->state(['status' => 'published']);
    }

    /** @return array<int, array<string, string>>|null */
    private function optionsForType(string $type): ?array
    {
        if ($type !== 'multiple_choice') {
            return null;
        }

        return [
            ['key' => 'A', 'text' => fake()->sentence(5)],
            ['key' => 'B', 'text' => fake()->sentence(5)],
            ['key' => 'C', 'text' => fake()->sentence(5)],
            ['key' => 'D', 'text' => fake()->sentence(5)],
        ];
    }

    private function createSkill(): BnccSkill
    {
        $grade = Grade::factory()->create();
        $subject = Subject::factory()->create();

        return BnccSkill::factory()->create(['grade_id' => $grade->id, 'subject_id' => $subject->id]);
    }
}
