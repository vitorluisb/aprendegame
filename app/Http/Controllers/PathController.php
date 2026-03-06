<?php

namespace App\Http\Controllers;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Services\PathProgressService;
use App\Domain\Gameplay\Models\LessonRun;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PathController extends Controller
{
    public function __construct(private readonly PathProgressService $pathProgressService) {}

    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $hasStudentProfile = $user->studentProfile()->withoutGlobalScopes()->exists();
        $isStudentContext = $user->role === UserRole::Student->value || $hasStudentProfile;
        $student = null;

        if ($isStudentContext) {
            $student = $user->ensureStudentProfile();
        }

        $pathsQuery = Path::query()
            ->where('published', true)
            ->whereIn('path_type', Path::TYPES)
            ->with(['grade', 'subject'])
            ->orderBy('path_type');

        if ($student?->grade_id) {
            $pathsQuery->where('grade_id', $student->grade_id);
        }

        $paths = $pathsQuery
            ->orderByRaw('(SELECT `order` FROM grades WHERE grades.id = paths.grade_id)')
            ->get()
            ->map(fn (Path $path) => [
                'id' => $path->id,
                'title' => $path->title,
                'path_type' => $path->path_type,
                'grade' => ['name' => $path->grade->name, 'stage' => $path->grade->stage],
                'subject' => ['name' => $path->subject->name, 'slug' => $path->subject->slug, 'color' => $path->subject->color, 'icon' => $path->subject->icon],
                'node_count' => $path->nodes()->where('published', true)->count(),
            ]);

        return Inertia::render('Paths/Index', [
            'paths' => $paths,
            'grade_filter' => $student?->grade_id,
        ]);
    }

    public function show(Path $path): Response
    {
        abort_unless($path->published, 404);

        $path->load(['grade', 'subject']);

        /** @var User $user */
        $user = auth()->user();
        $hasStudentProfile = $user->studentProfile()->withoutGlobalScopes()->exists();
        $isStudentContext = $user->role === UserRole::Student->value || $hasStudentProfile;
        $student = null;

        if ($isStudentContext) {
            $student = $user->ensureStudentProfile();
        }

        $publishedNodes = $path->nodes()
            ->where('published', true)
            ->with(['lessons' => fn ($q) => $q->where('published', true)->select('lessons.id', 'lessons.node_id', 'lessons.title', 'lessons.difficulty', 'lessons.interaction_count')])
            ->get()
            ->sortBy('order')
            ->values();

        $nodeIds = $publishedNodes->pluck('id');
        $lessonIds = $publishedNodes->pluck('lessons')->flatten()->pluck('id');

        $nodeProgress = collect();
        $completedLessonIds = collect();

        if ($student instanceof Student && $nodeIds->isNotEmpty()) {
            $nodeProgress = LessonRun::query()
                ->selectRaw('lessons.node_id as node_id, COALESCE(SUM(lesson_runs.total_count), 0) as answered_count')
                ->join('lessons', 'lessons.id', '=', 'lesson_runs.lesson_id')
                ->where('lesson_runs.student_id', $student->id)
                ->whereIn('lessons.node_id', $nodeIds)
                ->groupBy('lessons.node_id')
                ->pluck('answered_count', 'node_id');

            if ($lessonIds->isNotEmpty()) {
                $completedLessonIds = LessonRun::query()
                    ->where('student_id', $student->id)
                    ->whereIn('lesson_id', $lessonIds)
                    ->where('score', '>=', 70)
                    ->pluck('lesson_id');
            }
        }

        $totalNodes = $publishedNodes->count();

        $nodes = $publishedNodes
            ->map(function ($node) use ($completedLessonIds, $nodeProgress, $student, $totalNodes) {
                $isCompleted = $node->lessons->pluck('id')->intersect($completedLessonIds)->isNotEmpty();
                $progressQuestions = min((int) ($nodeProgress[$node->id] ?? 0), 10);
                $status = $isCompleted
                    ? 'completed'
                    : (($student instanceof Student)
                        ? $this->pathProgressService->getNodeStatus($node, $student)
                        : 'unlocked');
                $isBoss = $node->order === $totalNodes || $node->node_type === 'boss';
                $xpTotal = match (true) {
                    $isBoss => 120,
                    $node->node_type === 'review' => 100,
                    $node->order === 1 => 80,
                    default => 100,
                };

                $xpEarned = $isCompleted
                    ? $xpTotal
                    : (int) floor(($progressQuestions / 10) * $xpTotal);

                return [
                    'id' => $node->id,
                    'title' => $node->title,
                    'node_type' => $node->node_type,
                    'order' => $node->order,
                    'status' => $status,
                    'is_boss' => $isBoss,
                    'lesson_count' => $node->lessons->count(),
                    'question_target' => 10,
                    'progress_questions' => $progressQuestions,
                    'xp_total' => $xpTotal,
                    'xp_earned' => $xpEarned,
                    'stars' => $isCompleted ? 3 : ($progressQuestions >= 7 ? 2 : ($progressQuestions >= 3 ? 1 : 0)),
                    'lessons' => $node->lessons->map(fn ($lesson) => [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'difficulty' => $lesson->difficulty,
                    ])->values(),
                    'primary_lesson_id' => $node->lessons->first()?->id,
                ];
            })
            ->values();

        $totalXp = $nodes->sum('xp_total');
        $earnedXp = $nodes->sum('xp_earned');
        $currentNodeOrder = (int) ($nodes->firstWhere('status', 'unlocked')['order'] ?? $totalNodes);

        return Inertia::render('Paths/Show', [
            'path' => [
                'id' => $path->id,
                'title' => $path->title,
                'path_type' => $path->path_type,
                'grade' => $path->grade->name,
                'grade_stage' => $path->grade->stage,
                'subject' => $path->subject->name,
                'subject_slug' => $path->subject->slug,
                'subject_icon' => $path->subject->icon,
                'subject_color' => $path->subject->color,
                'total_xp' => $totalXp,
                'earned_xp' => $earnedXp,
                'current_node_order' => $currentNodeOrder,
            ],
            'nodes' => $nodes,
        ]);
    }
}
