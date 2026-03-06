<?php

namespace App\Http\Controllers;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Question;
use App\Domain\Gameplay\Services\LessonService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class LessonController extends Controller
{
    public function __construct(private readonly LessonService $lessonService) {}

    public function play(Lesson $lesson): Response
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();
        $student->refillLivesIfDue();
        $student = $student->fresh();

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
            'lives_current' => $student->lives_current,
            'lives_max' => $student->lives_max,
            'questions' => $questions,
        ]);
    }

    public function answer(Request $request, LessonRun $lessonRun): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();

        if ((int) $lessonRun->student_id !== (int) $student->id) {
            return response()->json(['message' => 'Acesso negado a esta sessão.'], 403);
        }

        $data = $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'answer' => ['required', 'string'],
            'time_ms' => ['required', 'integer', 'min:0'],
        ]);

        $question = $lessonRun->lesson?->questions()
            ->where('questions.id', $data['question_id'])
            ->first();

        if (! $question instanceof Question) {
            return response()->json([
                'message' => 'Questão não pertence a esta aula.',
                'remaining_lives' => $student->lives_current,
                'lives_max' => $student->lives_max,
            ], 422);
        }

        try {
            $attempt = $this->lessonService->answer($lessonRun, $question, $data['answer'], $data['time_ms']);
        } catch (RuntimeException $exception) {
            $student = Student::withoutGlobalScopes()->find($lessonRun->student_id);

            return response()->json([
                'message' => $exception->getMessage(),
                'remaining_lives' => $student?->lives_current ?? 0,
                'lives_max' => $student?->lives_max ?? 0,
            ], 422);
        }

        $student = Student::withoutGlobalScopes()->find($lessonRun->student_id);

        return response()->json([
            'correct' => $attempt->correct,
            'explanation' => $question->explanation,
            'correct_answer' => $question->correct_answer,
            'remaining_lives' => $student?->lives_current ?? 0,
            'lives_max' => $student?->lives_max ?? 0,
        ]);
    }

    public function finish(LessonRun $lessonRun): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $student = $user->ensureStudentProfile();

        if ((int) $lessonRun->student_id !== (int) $student->id) {
            return response()->json(['message' => 'Acesso negado a esta sessão.'], 403);
        }

        $run = $this->lessonService->finish($lessonRun);

        return response()->json([
            'score' => $run->score,
            'xp_earned' => $run->xp_earned,
            'neurons_earned' => (int) ($run->neurons_earned ?? 0),
            'correct_count' => $run->correct_count,
            'total_count' => $run->total_count,
        ]);
    }
}
