<?php

namespace App\Domain\Content\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\StudentPathProgress;

class PathUnlockService
{
    public function isUnlockedForStudent(Path $path, Student $student): bool
    {
        if (! $path->unlocks_after_path_id) {
            return true;
        }

        $requiredPathProgress = StudentPathProgress::query()
            ->where('student_id', $student->id)
            ->where('path_id', $path->unlocks_after_path_id)
            ->first();

        if (! $requiredPathProgress) {
            return false;
        }

        if ($requiredPathProgress->status === 'completed') {
            return true;
        }

        return $requiredPathProgress->xp_total > 0
            && $requiredPathProgress->xp_earned >= $requiredPathProgress->xp_total;
    }
}
