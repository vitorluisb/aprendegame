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
            ->with(['grade:id,name,stage,order', 'subject:id,name'])
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

                $missionCount = $this->resolveMissionCount($path);

                foreach (range(1, $missionCount) as $order) {
                    $isBossMission = $order === $missionCount;
                    $node = PathNode::query()->updateOrCreate(
                        [
                            'path_id' => $path->id,
                            'order' => $order,
                        ],
                        [
                            'title' => "Missão {$order} - {$path->subject->name}",
                            'node_type' => $isBossMission ? 'boss' : 'lesson',
                            'xp_reward' => $isBossMission ? 60 : 40,
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
                            'xp_reward' => $isBossMission ? 60 : 40,
                            'published' => true,
                        ]
                    );

                    $this->ensureLessonQuestions($lesson, $skillIds, 10);
                }
            });
    }

    private function resolveMissionCount(Path $path): int
    {
        $stage = $path->grade?->stage;
        $gradeOrder = (int) ($path->grade?->order ?? 0);

        if ($stage === 'fundamental_1') {
            return 15;
        }

        if ($stage === 'fundamental_2') {
            return 25;
        }

        if ($stage === 'medio') {
            return 30;
        }

        if ($stage === 'fundamental') {
            return $gradeOrder > 0 && $gradeOrder <= 3 ? 15 : 25;
        }

        if ($gradeOrder > 0 && $gradeOrder <= 3) {
            return 15;
        }

        if ($gradeOrder > 0 && $gradeOrder <= 7) {
            return 25;
        }

        return 30;
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
