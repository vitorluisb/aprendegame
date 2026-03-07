<?php

namespace Database\Seeders\Gameplay;

use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\Lesson;
use Illuminate\Database\Seeder;

class SyncTrailMissionCountSeeder extends Seeder
{
    public function run(): void
    {
        $allSkillIds = BnccSkill::query()
            ->where('active', true)
            ->pluck('id');

        Path::query()
            ->where('published', true)
            ->with(['grade:id,stage,order', 'subject:id,name'])
            ->get()
            ->each(function (Path $path) use ($allSkillIds): void {
                $missionCount = $this->resolveMissionCount($path);

                $skillIds = BnccSkill::query()
                    ->where('grade_id', $path->grade_id)
                    ->where('subject_id', $path->subject_id)
                    ->where('active', true)
                    ->limit(3)
                    ->pluck('id');

                if ($skillIds->isEmpty()) {
                    $skillIds = $allSkillIds->shuffle()->take(3)->values();
                }

                if ($skillIds->isEmpty()) {
                    return;
                }

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
                            'skill_ids' => $skillIds->values()->all(),
                            'published' => true,
                        ]
                    );

                    Lesson::query()->updateOrCreate(
                        [
                            'node_id' => $node->id,
                        ],
                        [
                            'title' => "Aula {$order} - {$path->subject->name}",
                            'lesson_type' => $isBossMission ? 'boss' : 'practice',
                            'order' => 1,
                            'interaction_count' => 10,
                            'difficulty' => min(5, max(1, (int) ceil($order / 6))),
                            'xp_reward' => $isBossMission ? 60 : 40,
                            'published' => true,
                        ]
                    );
                }
            });
    }

    private function resolveMissionCount(Path $path): int
    {
        $stage = (string) ($path->grade?->stage ?? '');
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
}
