<?php

namespace App\Http\Controllers;

use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Question;
use App\Domain\Gameplay\Services\LessonService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LessonController extends Controller
{
    public function __construct(private readonly LessonService $lessonService) {}

    public function play(Lesson $lesson): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();

        // Resume existing incomplete run, or start a new one
        $run = LessonRun::where('student_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->whereNull('finished_at')
            ->latest()
            ->first();

        if (! $run) {
            $run = $this->lessonService->start($student, $lesson);
        }

        $answeredIds = $run->attempts()->pluck('question_id')->toArray();

        $questions = $lesson->questions()
            ->get()
            ->map(fn (Question $q) => [
                'id' => $q->id,
                'type' => $q->type,
                'difficulty' => $q->difficulty,
                'prompt' => $q->prompt,
                'options' => $q->options,
                'answered' => in_array($q->id, $answeredIds),
            ]);

        return Inertia::render('Lessons/Play', [
            'lesson' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'path_title' => $lesson->node?->path?->title,
            ],
            'run_id' => $run->id,
            'questions' => $questions,
        ]);
    }

    public function answer(Request $request, LessonRun $lessonRun): JsonResponse
    {
        $data = $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'answer' => ['required', 'string'],
            'time_ms' => ['required', 'integer', 'min:0'],
        ]);

        $question = Question::findOrFail($data['question_id']);

        $attempt = $this->lessonService->answer($lessonRun, $question, $data['answer'], $data['time_ms']);

        return response()->json([
            'correct' => $attempt->correct,
            'explanation' => $question->explanation,
            'correct_answer' => $question->correct_answer,
        ]);
    }

    public function finish(LessonRun $lessonRun): JsonResponse
    {
        $run = $this->lessonService->finish($lessonRun);

        return response()->json([
            'score' => $run->score,
            'xp_earned' => $run->xp_earned,
            'correct_count' => $run->correct_count,
            'total_count' => $run->total_count,
        ]);
    }
}
