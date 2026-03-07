<?php

namespace App\Domain\Content\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\LessonRun;

class PathProgressService
{
    public function __construct(private readonly PathUnlockService $pathUnlockService) {}

    public function getNodeStatus(PathNode $node, Student $student): string
    {
        if (! $this->pathUnlockService->isUnlockedForStudent($node->path, $student)) {
            return 'locked';
        }

        if ($node->order === 1) {
            return 'unlocked';
        }

        $previousNode = PathNode::where('path_id', $node->path_id)
            ->where('order', $node->order - 1)
            ->first();

        if (! $previousNode) {
            return 'locked';
        }

        $lessonIds = \App\Domain\Gameplay\Models\Lesson::where('node_id', $previousNode->id)
            ->pluck('id');

        if ($lessonIds->isEmpty()) {
            return 'locked';
        }

        $completed = LessonRun::where('student_id', $student->id)
            ->whereIn('lesson_id', $lessonIds)
            ->where('score', '>=', 70)
            ->exists();

        return $completed ? 'unlocked' : 'locked';
    }
}
