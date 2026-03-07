<?php

namespace App\Http\Controllers;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Attempt;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Question;
use App\Domain\Gameplay\Services\LessonService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $targetQuestionCount = max(1, (int) ($lesson->interaction_count ?: 10));
        $questionPoolTarget = min(60, max($targetQuestionCount * 3, $targetQuestionCount + 10));
        $this->ensureLessonHasQuestionTarget($lesson, $questionPoolTarget);

        $answeredIds = $run->attempts()->pluck('question_id');
        $questions = $this->selectQuestionsForStudent($lesson, $student, $answeredIds, $targetQuestionCount)
            ->map(fn (Question $q) => [
                'id' => $q->id,
                'type' => $q->type,
                'difficulty' => $q->difficulty,
                'prompt' => $q->prompt,
                'options' => $this->normalizeOptionsForClient($q),
                'answered' => $answeredIds->contains($q->id),
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
            'correct_answer' => $this->resolveCorrectAnswerKeyForClient($question),
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

    private function ensureLessonHasQuestionTarget(Lesson $lesson, int $targetCount): void
    {
        $currentCount = $lesson->questions()->count();

        if ($currentCount >= $targetCount) {
            return;
        }

        $skillIds = $lesson->node?->skill_ids ?? [];

        if (! is_array($skillIds) || $skillIds === []) {
            return;
        }

        $attachedQuestionIds = $lesson->questions()->pluck('questions.id');
        $missingCount = $targetCount - $currentCount;
        $nextOrder = (int) ($lesson->questions()->max('lesson_questions.order') ?? 0);

        $publishedCandidates = Question::query()
            ->whereIn('skill_id', $skillIds)
            ->where('status', 'published')
            ->whereNotIn('id', $attachedQuestionIds)
            ->inRandomOrder()
            ->limit($missingCount)
            ->get(['id']);

        $candidateIds = $publishedCandidates->pluck('id');
        $remaining = $missingCount - $publishedCandidates->count();

        if ($remaining > 0) {
            $fallbackCandidates = Question::query()
                ->whereIn('skill_id', $skillIds)
                ->whereNotIn('id', $attachedQuestionIds->merge($candidateIds))
                ->inRandomOrder()
                ->limit($remaining)
                ->get(['id']);

            $candidateIds = $candidateIds->merge($fallbackCandidates->pluck('id'));
        }

        $candidates = Question::query()
            ->whereIn('id', $candidateIds)
            ->get(['id']);

        foreach ($candidates as $candidate) {
            $nextOrder++;
            $lesson->questions()->attach($candidate->id, ['order' => $nextOrder]);
        }
    }

    /**
     * @param  Collection<int, int>  $answeredIds
     * @return Collection<int, Question>
     */
    private function selectQuestionsForStudent(Lesson $lesson, Student $student, Collection $answeredIds, int $targetCount): Collection
    {
        $skillIds = collect($lesson->node?->skill_ids ?? [])
            ->filter(fn (mixed $skillId): bool => is_numeric($skillId))
            ->map(fn (mixed $skillId): int => (int) $skillId)
            ->values();

        $recentQuestionIds = Attempt::query()
            ->where('student_id', $student->id)
            ->when(
                $skillIds->isNotEmpty(),
                fn ($query) => $query->whereHas('question', fn ($questionQuery) => $questionQuery->whereIn('skill_id', $skillIds)),
                fn ($query) => $query->whereIn('question_id', $lesson->questions()->pluck('questions.id'))
            )
            ->orderByDesc('id')
            ->limit(300)
            ->pluck('question_id');

        $baseQuery = $lesson->questions()
            ->whereNotIn('questions.id', $answeredIds);

        $selected = (clone $baseQuery)
            ->when(
                $recentQuestionIds->isNotEmpty(),
                fn ($query) => $query->whereNotIn('questions.id', $recentQuestionIds)
            )
            ->inRandomOrder()
            ->limit($targetCount)
            ->get();

        $remainingCount = $targetCount - $selected->count();

        if ($remainingCount <= 0) {
            return $selected;
        }

        $fallback = (clone $baseQuery)
            ->whereNotIn('questions.id', $selected->pluck('id'))
            ->inRandomOrder()
            ->limit($remainingCount)
            ->get();

        return $selected->concat($fallback)->values();
    }

    /**
     * @return array<int, array{key: string, text: string}>
     */
    private function normalizeOptionsForClient(Question $question): array
    {
        if ($question->type === 'true_false') {
            return [
                ['key' => 'TRUE', 'text' => 'Verdadeiro'],
                ['key' => 'FALSE', 'text' => 'Falso'],
            ];
        }

        $rawOptions = $question->options;

        if (! is_array($rawOptions)) {
            return [];
        }

        $entries = [];

        if (! array_is_list($rawOptions)) {
            foreach ($rawOptions as $key => $text) {
                $entries[] = [
                    'key' => strtoupper(trim((string) $key)),
                    'text' => trim((string) $text),
                ];
            }

            return $entries;
        }

        foreach ($rawOptions as $index => $option) {
            if (is_array($option)) {
                $key = strtoupper(trim((string) ($option['key'] ?? '')));
                $text = trim((string) ($option['text'] ?? ''));
            } else {
                $key = chr(65 + (int) $index);
                $text = trim((string) $option);
            }

            if ($key === '') {
                $key = chr(65 + (int) $index);
            }

            $entries[] = [
                'key' => $key,
                'text' => $text,
            ];
        }

        return $entries;
    }

    private function resolveCorrectAnswerKeyForClient(Question $question): string
    {
        $normalizedCorrectAnswer = strtolower(trim((string) $question->correct_answer));

        if ($question->type === 'true_false') {
            return in_array($normalizedCorrectAnswer, ['verdadeiro', 'true', 'v'], true)
                ? 'TRUE'
                : 'FALSE';
        }

        $normalizedOptions = $this->normalizeOptionsForClient($question);

        foreach ($normalizedOptions as $option) {
            if (strtolower($option['key']) === $normalizedCorrectAnswer) {
                return $option['key'];
            }

            if (strtolower(trim($option['text'])) === $normalizedCorrectAnswer) {
                return $option['key'];
            }
        }

        return strtoupper(trim((string) $question->correct_answer));
    }
}
