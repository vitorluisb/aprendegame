<?php

namespace App\Http\Controllers\Dashboard;

use App\Domain\Content\Models\Path;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentItem;
use App\Domain\Gameplay\Services\MasteryService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly MasteryService $masteryService) {}

    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->role === 'guardian') {
            return $this->guardianDashboard($user);
        }

        return $this->studentDashboard($user);
    }

    private function guardianDashboard(User $user): Response
    {
        $students = $user->studentsGuarded()
            ->withoutGlobalScopes()
            ->with(['streak'])
            ->get(['students.id', 'students.name', 'students.avatar_url']);

        return Inertia::render('Dashboard/Index', [
            'role' => 'guardian',
            'students' => $students,
        ]);
    }

    private function studentDashboard(User $user): Response
    {
        $student = $user->ensureStudentProfile();
        $student->refillLivesIfDue();
        $student = $student->fresh()->load('streak');
        $equippedAvatarUrl = StudentItem::query()
            ->where('student_id', $student->id)
            ->where('equipped', true)
            ->whereHas('item', fn ($query) => $query->whereIn('type', ShopItem::rawTypeCandidates(ShopItem::TYPE_AVATAR))->whereNotNull('image_url'))
            ->with('item:id,image_url')
            ->latest('updated_at')
            ->first()
            ?->item?->image_url;
        $displayAvatarUrl = $equippedAvatarUrl ?: $this->normalizeStudentAvatarUrl($student->avatar_url);

        $totalXp = $student->totalXp();
        $level = (int) floor($totalXp / 100) + 1;
        $xpInLevel = $totalXp % 100;

        $dueReviews = $this->masteryService->getDueReviews($student, 5);
        $lastLessonRun = LessonRun::query()
            ->where('student_id', $student->id)
            ->whereNotNull('finished_at')
            ->with([
                'lesson:id,node_id,title',
                'lesson.node:id,path_id',
                'lesson.node.path:id,title',
            ])
            ->latest('finished_at')
            ->first();

        $recommendedPathsQuery = Path::query()
            ->where('published', true)
            ->whereIn('path_type', Path::TYPES)
            ->with(['grade', 'subject'])
            ->orderBy('path_type')
            ->orderByRaw('(SELECT `order` FROM grades WHERE grades.id = paths.grade_id)')
            ->limit(4);

        if ($student->grade_id) {
            $recommendedPathsQuery->where('grade_id', $student->grade_id);
        }

        $recommendedPaths = $recommendedPathsQuery
            ->get()
            ->map(fn (Path $path): array => [
                'id' => $path->id,
                'title' => $path->title,
                'path_type' => $path->path_type,
                'grade' => [
                    'id' => $path->grade->id,
                    'name' => $path->grade->name,
                ],
                'subject' => [
                    'name' => $path->subject->name,
                    'color' => $path->subject->color,
                    'icon' => $path->subject->icon,
                ],
            ]);

        return Inertia::render('Dashboard/Index', [
            'role' => 'student',
            'student' => [
                'name' => $student->name,
                'avatar_url' => $displayAvatarUrl,
                'total_xp' => $totalXp,
                'level' => $level,
                'xp_in_level' => $xpInLevel,
                'streak_current' => $student->streak?->current ?? 0,
                'streak_best' => $student->streak?->best ?? 0,
                'lives_current' => $student->lives_current,
                'lives_max' => $student->lives_max,
                'due_reviews_count' => $dueReviews->count(),
                'total_gems' => $student->totalGems(),
                'due_reviews' => $dueReviews->map(fn ($m) => [
                    'skill_code' => $m->skill?->code,
                    'skill_description' => $m->skill?->description,
                    'mastery_score' => $m->mastery_score,
                ]),
                'last_activity' => $lastLessonRun ? [
                    'path_title' => $lastLessonRun->lesson?->node?->path?->title,
                    'lesson_title' => $lastLessonRun->lesson?->title,
                    'xp_earned' => $lastLessonRun->xp_earned,
                    'finished_at' => $lastLessonRun->finished_at?->toIso8601String(),
                ] : null,
                'recommended_paths' => $recommendedPaths,
            ],
        ]);
    }

    private function normalizeStudentAvatarUrl(?string $avatarUrl): ?string
    {
        if (! $avatarUrl) {
            return null;
        }

        if (str_starts_with($avatarUrl, '/storage/student-avatars/')) {
            return '/media/student-avatars/'.basename($avatarUrl);
        }

        return $avatarUrl;
    }
}
