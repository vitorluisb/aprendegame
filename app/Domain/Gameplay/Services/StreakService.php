<?php

namespace App\Domain\Gameplay\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Streak;

class StreakService
{
    public function update(Student $student): Streak
    {
        $streak = Streak::firstOrCreate(['student_id' => $student->id]);
        $today = today();
        $yesterday = today()->subDay();

        if ($streak->last_activity_date === null) {
            $streak->update(['current' => 1, 'last_activity_date' => $today, 'best' => 1]);
        } elseif ($streak->last_activity_date->equalTo($today)) {
            // Já estudou hoje — não incrementar
        } elseif ($streak->last_activity_date->equalTo($yesterday)) {
            $newCurrent = $streak->current + 1;
            $streak->update([
                'current' => $newCurrent,
                'last_activity_date' => $today,
                'best' => max($streak->best, $newCurrent),
            ]);
        } else {
            // Quebrou — verificar freeze disponível
            if ($this->canUseFreeze($streak)) {
                $streak->update(['freeze_used_at' => now()]);
                // Mantém streak mas não incrementa
            } else {
                $streak->update(['current' => 1, 'last_activity_date' => $today]);
            }
        }

        return $streak->refresh();
    }

    private function canUseFreeze(Streak $streak): bool
    {
        if (! $streak->freeze_used_at) {
            return false;
        }

        // Freeze disponível se não usou na semana atual
        return $streak->freeze_used_at->lt(now()->startOfWeek());
    }
}
