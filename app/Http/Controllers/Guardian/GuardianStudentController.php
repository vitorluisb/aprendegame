<?php

namespace App\Http\Controllers\Guardian;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\LessonRun;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GuardianStudentController extends Controller
{
    public function show(Student $student): Response|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $isGuardian = $user->studentsGuarded()
            ->withoutGlobalScopes()
            ->where('students.id', $student->id)
            ->exists();

        if (! $isGuardian) {
            abort(403);
        }

        $student->loadMissing(['streak', 'grade']);

        $totalXp = $student->totalXp();
        $level = (int) floor($totalXp / 100) + 1;
        $xpInLevel = $totalXp % 100;

        $days = collect(range(13, 0))->map(fn (int $i): string => now()->subDays($i)->toDateString());

        $xpByDay = LessonRun::query()
            ->where('student_id', $student->id)
            ->whereNotNull('finished_at')
            ->where('finished_at', '>=', now()->subDays(14)->startOfDay())
            ->selectRaw('DATE(finished_at) as date, SUM(xp_earned) as xp_earned')
            ->groupBy('date')
            ->pluck('xp_earned', 'date');

        $lessonsByDay = LessonRun::query()
            ->where('student_id', $student->id)
            ->whereNotNull('finished_at')
            ->where('finished_at', '>=', now()->subDays(14)->startOfDay())
            ->selectRaw('DATE(finished_at) as date, COUNT(*) as lessons_count')
            ->groupBy('date')
            ->pluck('lessons_count', 'date');

        $chartData = $days->map(fn (string $date): array => [
            'date' => $date,
            'xp' => (int) ($xpByDay[$date] ?? 0),
            'lessons' => (int) ($lessonsByDay[$date] ?? 0),
        ])->values();

        $totalLessons = LessonRun::query()
            ->where('student_id', $student->id)
            ->whereNotNull('finished_at')
            ->count();

        $recentActivity = LessonRun::query()
            ->where('student_id', $student->id)
            ->whereNotNull('finished_at')
            ->with([
                'lesson:id,title,node_id',
                'lesson.node:id,path_id',
                'lesson.node.path:id,title',
            ])
            ->latest('finished_at')
            ->limit(10)
            ->get()
            ->map(fn (LessonRun $run): array => [
                'id' => $run->id,
                'lesson_title' => $run->lesson?->title,
                'path_title' => $run->lesson?->node?->path?->title,
                'xp_earned' => $run->xp_earned,
                'score' => $run->score,
                'finished_at' => $run->finished_at?->toIso8601String(),
            ]);

        return Inertia::render('Guardian/StudentDetail', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'avatar_url' => $student->avatar_url,
                'grade' => $student->grade?->name,
                'total_xp' => $totalXp,
                'level' => $level,
                'xp_in_level' => $xpInLevel,
                'streak_current' => $student->streak?->current ?? 0,
                'streak_best' => $student->streak?->best ?? 0,
                'total_lessons' => $totalLessons,
            ],
            'chart_data' => $chartData,
            'recent_activity' => $recentActivity,
        ]);
    }
}
