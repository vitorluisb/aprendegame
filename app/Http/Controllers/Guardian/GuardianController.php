<?php

namespace App\Http\Controllers\Guardian;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\LessonRun;
use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class GuardianController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $students = $user->studentsGuarded()
            ->withoutGlobalScopes()
            ->with(['streak', 'grade'])
            ->get();

        $studentsData = $students->map(function (Student $student): array {
            $totalXp = $student->totalXp();
            $level = (int) floor($totalXp / 100) + 1;

            $lessonsThisWeek = LessonRun::query()
                ->where('student_id', $student->id)
                ->whereNotNull('finished_at')
                ->where('finished_at', '>=', now()->startOfWeek())
                ->count();

            $xpThisWeek = (int) LessonRun::query()
                ->where('student_id', $student->id)
                ->whereNotNull('finished_at')
                ->where('finished_at', '>=', now()->startOfWeek())
                ->sum('xp_earned');

            return [
                'id' => $student->id,
                'name' => $student->name,
                'avatar_url' => $student->avatar_url,
                'grade' => $student->grade?->name,
                'total_xp' => $totalXp,
                'level' => $level,
                'streak_current' => $student->streak?->current ?? 0,
                'lessons_this_week' => $lessonsThisWeek,
                'xp_this_week' => $xpThisWeek,
            ];
        });

        return Inertia::render('Guardian/Dashboard', [
            'students' => $studentsData,
        ]);
    }
}
