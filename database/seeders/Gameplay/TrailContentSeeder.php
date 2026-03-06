<?php

namespace Database\Seeders\Gameplay;

use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TrailContentSeeder extends Seeder
{
    public function run(): void
    {
        $fallbackSkillIds = BnccSkill::query()->pluck('id');

        Path::query()
            ->where('published', true)
            ->with(['grade:id,name', 'subject:id,name'])
            ->orderBy('id')
            ->get()
            ->each(function (Path $path) use ($fallbackSkillIds): void {
                $pathSkillIds = BnccSkill::query()
                    ->where('grade_id', $path->grade_id)
                    ->where('subject_id', $path->subject_id)
                    ->limit(6)
                    ->pluck('id');

                $skillIds = $pathSkillIds->isNotEmpty()
                    ? $pathSkillIds
                    : $fallbackSkillIds->shuffle()->take(6)->values();

                if ($skillIds->isEmpty()) {
                    return;
                }

                foreach ([1, 2, 3] as $order) {
                    $node = PathNode::query()->updateOrCreate(
                        [
                            'path_id' => $path->id,
                            'order' => $order,
                        ],
                        [
                            'title' => "Módulo {$order} - {$path->subject->name}",
                            'node_type' => $order === 3 ? 'boss' : 'lesson',
                            'skill_ids' => $skillIds->take(3)->values()->all(),
                            'published' => true,
                        ]
                    );

                    $lesson = Lesson::query()->updateOrCreate(
                        [
                            'node_id' => $node->id,
                        ],
                        [
                            'title' => "Aula {$order} - {$path->subject->name}",
                            'interaction_count' => 10,
                            'difficulty' => min(5, $order + 1),
                            'published' => true,
                        ]
                    );

                    $this->ensureLessonQuestions($lesson, $skillIds, 10);
                }
            });
    }

    private function ensureLessonQuestions(Lesson $lesson, Collection $skillIds, int $targetCount): void
    {
        $existingQuestionCount = $lesson->questions()->count();
        $missingCount = $targetCount - $existingQuestionCount;

        if ($missingCount <= 0) {
            return;
        }

        $nextOrder = $existingQuestionCount + 1;

        for ($index = 1; $index <= $missingCount; $index++) {
            $optionA = fake()->sentence(3);
            $optionB = fake()->sentence(3);
            $optionC = fake()->sentence(3);
            $optionD = fake()->sentence(3);
            $options = [$optionA, $optionB, $optionC, $optionD];
            $correctAnswer = $options[array_rand($options)];

            $question = Question::query()->create([
                'skill_id' => $skillIds->random(),
                'type' => 'multiple_choice',
                'difficulty' => fake()->numberBetween(1, 5),
                'prompt' => fake()->sentence(10).'?',
                'options' => $options,
                'correct_answer' => $correctAnswer,
                'explanation' => fake()->sentence(14),
                'status' => 'published',
                'ai_generated' => false,
            ]);

            $lesson->questions()->attach($question->id, ['order' => $nextOrder]);
            $nextOrder++;
        }
    }
}
