<?php

namespace App\Http\Controllers;

use App\Domain\Content\Models\Path;
use App\Domain\Accounts\Enums\UserRole;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PathController extends Controller
{
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
        $nodes = $path->nodes()
            ->where('published', true)
            ->with(['lessons' => fn ($q) => $q->where('published', true)->select('lessons.id', 'lessons.node_id', 'lessons.title', 'lessons.difficulty', 'lessons.interaction_count')])
            ->get()
            ->map(fn ($node) => [
                'id' => $node->id,
                'title' => $node->title,
                'node_type' => $node->node_type,
                'order' => $node->order,
                'lesson_count' => $node->lessons->count(),
                'lessons' => $node->lessons->map(fn ($lesson) => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'difficulty' => $lesson->difficulty,
                ]),
            ]);

        return Inertia::render('Paths/Show', [
            'path' => [
                'id' => $path->id,
                'title' => $path->title,
                'path_type' => $path->path_type,
                'grade' => $path->grade->name,
                'subject' => $path->subject->name,
                'subject_color' => $path->subject->color,
            ],
            'nodes' => $nodes,
        ]);
    }
}
