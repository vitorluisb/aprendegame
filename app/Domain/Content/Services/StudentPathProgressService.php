<?php

namespace App\Domain\Content\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\StudentPathProgress;
use App\Domain\Gameplay\Models\LessonRun;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class StudentPathProgressService
{
    public function __construct(private readonly PathUnlockService $pathUnlockService) {}

    /**
     * @return array{
     *     status: string,
     *     current_node_order: int,
     *     xp_earned: int,
     *     xp_total: int,
     *     stars: int,
     *     accuracy_percent: float|null,
     *     attempts_count: int,
     *     started_at: CarbonInterface|null,
     *     completed_at: CarbonInterface|null
     * }
     */
    public function summarize(Path $path, Student $student): array
    {
        $publishedNodes = $path->nodes()
            ->where('published', true)
            ->with(['lessons' => fn ($query) => $query
                ->where('published', true)
                ->select('lessons.id', 'lessons.node_id', 'lessons.xp_reward')])
            ->get();

        if ($publishedNodes->isEmpty()) {
            return [
                'status' => $this->pathUnlockService->isUnlockedForStudent($path, $student) ? 'unlocked' : 'locked',
                'current_node_order' => 1,
                'xp_earned' => 0,
                'xp_total' => 0,
                'stars' => 0,
                'accuracy_percent' => null,
                'attempts_count' => 0,
                'started_at' => null,
                'completed_at' => null,
            ];
        }

        $lessonIds = $publishedNodes->pluck('lessons')->flatten()->pluck('id')->values();

        if ($lessonIds->isEmpty()) {
            return [
                'status' => $this->pathUnlockService->isUnlockedForStudent($path, $student) ? 'unlocked' : 'locked',
                'current_node_order' => (int) $publishedNodes->min('order'),
                'xp_earned' => 0,
                'xp_total' => 0,
                'stars' => 0,
                'accuracy_percent' => null,
                'attempts_count' => 0,
                'started_at' => null,
                'completed_at' => null,
            ];
        }

        $lessonRuns = LessonRun::query()
            ->where('student_id', $student->id)
            ->whereIn('lesson_id', $lessonIds)
            ->whereNotNull('finished_at')
            ->get([
                'lesson_id',
                'score',
                'total_count',
                'correct_count',
                'started_at',
                'finished_at',
            ]);

        $completedLessonIds = $lessonRuns
            ->filter(fn (LessonRun $run) => $run->score >= 70)
            ->pluck('lesson_id')
            ->unique();

        $lessonNodeMap = $publishedNodes
            ->pluck('lessons')
            ->flatten()
            ->pluck('node_id', 'id');

        $questionProgressByNode = $lessonRuns
            ->groupBy(fn (LessonRun $run) => (int) ($lessonNodeMap[$run->lesson_id] ?? 0))
            ->reject(fn ($runs, int $nodeId) => $nodeId === 0)
            ->map(fn ($runs) => min((int) $runs->sum('total_count'), 10));

        $xpTotal = (int) $publishedNodes->sum(fn ($node) => (int) $node->xp_reward);
        $xpEarned = 0;
        $completedNodes = 0;

        foreach ($publishedNodes as $node) {
            $nodeLessonIds = $node->lessons->pluck('id');
            $nodeCompleted = $nodeLessonIds->intersect($completedLessonIds)->isNotEmpty();
            $nodeProgressQuestions = (int) ($questionProgressByNode[$node->id] ?? 0);
            $nodeXpReward = (int) $node->xp_reward;

            if ($nodeCompleted) {
                $completedNodes++;
                $xpEarned += $nodeXpReward;

                continue;
            }

            $xpEarned += (int) floor(($nodeProgressQuestions / 10) * $nodeXpReward);
        }

        $attemptsCount = (int) $lessonRuns->sum('total_count');
        $correctCount = (int) $lessonRuns->sum('correct_count');
        $accuracyPercent = $attemptsCount > 0
            ? round(($correctCount / $attemptsCount) * 100, 2)
            : null;
        $stars = $this->resolveStars($accuracyPercent);
        $isUnlocked = $this->pathUnlockService->isUnlockedForStudent($path, $student);
        $status = $this->resolveStatus($isUnlocked, $attemptsCount, $completedNodes, $publishedNodes->count());
        $currentNodeOrder = $this->resolveCurrentNodeOrder($publishedNodes, $completedLessonIds);
        $startedAt = $lessonRuns->min('started_at');
        $completedAt = $status === 'completed' ? $lessonRuns->max('finished_at') : null;

        return [
            'status' => $status,
            'current_node_order' => $currentNodeOrder,
            'xp_earned' => $xpEarned,
            'xp_total' => $xpTotal,
            'stars' => $stars,
            'accuracy_percent' => $accuracyPercent,
            'attempts_count' => $attemptsCount,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
        ];
    }

    public function sync(Path $path, Student $student): StudentPathProgress
    {
        $summary = $this->summarize($path, $student);

        return StudentPathProgress::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'path_id' => $path->id,
            ],
            $summary
        );
    }

    private function resolveStatus(bool $isUnlocked, int $attemptsCount, int $completedNodes, int $totalNodes): string
    {
        if (! $isUnlocked) {
            return 'locked';
        }

        if ($completedNodes > 0 && $completedNodes === $totalNodes) {
            return 'completed';
        }

        if ($attemptsCount > 0) {
            return 'in_progress';
        }

        return 'unlocked';
    }

    private function resolveCurrentNodeOrder(Collection $publishedNodes, Collection $completedLessonIds): int
    {
        foreach ($publishedNodes as $node) {
            $nodeCompleted = $node->lessons->pluck('id')->intersect($completedLessonIds)->isNotEmpty();

            if (! $nodeCompleted) {
                return (int) $node->order;
            }
        }

        return (int) $publishedNodes->max('order');
    }

    private function resolveStars(?float $accuracyPercent): int
    {
        if ($accuracyPercent === null) {
            return 0;
        }

        if ($accuracyPercent >= 90) {
            return 3;
        }

        if ($accuracyPercent >= 70) {
            return 2;
        }

        if ($accuracyPercent >= 40) {
            return 1;
        }

        return 0;
    }
}
