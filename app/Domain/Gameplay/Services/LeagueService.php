<?php

namespace App\Domain\Gameplay\Services;

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\LeagueSnapshot;
use Illuminate\Support\Facades\Redis;

class LeagueService
{
    public function addXP(Student $student, int $xp): void
    {
        $key = $this->weeklyKey($student->school_id);
        Redis::zincrby($key, $xp, $student->id);
        Redis::expire($key, 60 * 60 * 24 * 14); // manter por 14 dias
    }

    public function getLeaderboard(?int $schoolId, int $limit = 20): array
    {
        $key = $this->weeklyKey($schoolId);

        // phpredis: true = WITHSCORES, retorna [member => score]
        return Redis::zrevrange($key, 0, $limit - 1, true);
    }

    public function snapshotAndReset(): void
    {
        $schools = School::where('active', true)->get();

        foreach ($schools as $school) {
            $key = $this->weeklyKey($school->id);
            $scores = Redis::zrevrange($key, 0, -1, true);

            if (! empty($scores)) {
                $this->persistSnapshot($school->id, $scores);
                Redis::del($key);
            }
        }
    }

    private function weeklyKey(?int $schoolId): string
    {
        $week = now()->weekOfYear;
        $year = now()->year;

        return "league:school:{$schoolId}:week:{$year}:{$week}";
    }

    /** @param array<string, string> $scores student_id => xp */
    private function persistSnapshot(int $schoolId, array $scores): void
    {
        $total = count($scores);
        $rank = 1;

        foreach ($scores as $studentId => $xp) {
            LeagueSnapshot::create([
                'student_id' => (int) $studentId,
                'school_id' => $schoolId,
                'league' => $this->resolveLeague($rank, $total),
                'weekly_xp' => (int) $xp,
                'rank_position' => $rank,
                'week' => now()->weekOfYear,
                'year' => now()->year,
            ]);

            $rank++;
        }
    }

    private function resolveLeague(int $rank, int $total): string
    {
        if ($total <= 0) {
            return 'bronze';
        }

        $percentile = $rank / $total;

        return match (true) {
            $rank <= 3 => 'platinum',
            $percentile <= 0.25 => 'gold',
            $percentile <= 0.60 => 'silver',
            default => 'bronze',
        };
    }
}
