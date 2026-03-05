<?php

namespace App\Domain\Gameplay\Services;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Mastery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MasteryService
{
    // Intervalos em dias: 1 → 3 → 7 → 14 → 30
    private const INTERVALS = [1, 3, 7, 14, 30];

    public function forgetCache(Student $student): void
    {
        Cache::forget("mastery:due:{$student->id}");
    }

    public function update(Student $student, int $skillId, bool $correct): Mastery
    {
        $this->forgetCache($student);

        $mastery = Mastery::firstOrCreate(
            ['student_id' => $student->id, 'skill_id' => $skillId],
            ['mastery_score' => 0, 'interval_days' => 1, 'consecutive_correct' => 0]
        );

        if ($correct) {
            $consecutive = $mastery->consecutive_correct + 1;
            $newScore = min(100, $mastery->mastery_score + 10);
            $newInterval = $this->nextInterval($mastery->interval_days, $consecutive);
        } else {
            $consecutive = 0;
            $newScore = max(0, $mastery->mastery_score - 15);
            $newInterval = 1; // voltar ao início
        }

        $mastery->update([
            'mastery_score' => $newScore,
            'consecutive_correct' => $consecutive,
            'interval_days' => $newInterval,
            'last_seen_at' => now(),
            'next_review_at' => now()->addDays($newInterval),
        ]);

        return $mastery->refresh();
    }

    public function getDueReviews(Student $student, int $limit = 5): Collection
    {
        $cacheKey = "mastery:due:{$student->id}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($student, $limit) {
            return Mastery::where('student_id', $student->id)
                ->where('next_review_at', '<=', now())
                ->where('mastery_score', '<', 90)
                ->orderBy('next_review_at')
                ->limit($limit)
                ->with('skill')
                ->get();
        });
    }

    private function nextInterval(int $current, int $consecutive): int
    {
        $index = array_search($current, self::INTERVALS);
        $index = $index === false ? 0 : $index;
        $nextIndex = min($consecutive >= 2 ? $index + 1 : $index, count(self::INTERVALS) - 1);

        return self::INTERVALS[$nextIndex];
    }
}
