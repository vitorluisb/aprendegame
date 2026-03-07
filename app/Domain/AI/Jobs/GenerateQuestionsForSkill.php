<?php

namespace App\Domain\AI\Jobs;

use App\Domain\AI\Models\AiJob;
use App\Domain\AI\Services\AIService;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class GenerateQuestionsForSkill implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(
        private readonly int $skillId,
        private readonly int $count = 10,
        private readonly string $model = AIService::DEFAULT_MODEL,
        private readonly ?int $aiJobId = null,
    ) {
        $this->onQueue('ai');
    }

    public function backoff(): array
    {
        return [10, 30, 90];
    }

    public function handle(AIService $ai): void
    {
        $requestedCount = max(1, min(50, $this->count));
        $skill = BnccSkill::with(['grade', 'subject'])->findOrFail($this->skillId);
        $aiJob = $this->resolveAiJob($requestedCount);
        $lessonOrders = $this->resolveLessonOrdersForSkill();

        try {
            $questions = $ai->generateQuestions($skill, $requestedCount, $this->model);

            foreach ($questions as $q) {
                $payload = $this->normalizeQuestionPayload($q);

                $question = Question::create([
                    'skill_id' => $this->skillId,
                    'type' => $payload['type'],
                    'difficulty' => $payload['difficulty'],
                    'prompt' => $payload['prompt'],
                    'options' => $payload['options'],
                    'correct_answer' => $payload['correct_answer'],
                    'explanation' => $payload['explanation'],
                    'status' => 'draft',
                    'ai_generated' => true,
                ]);

                $this->attachQuestionToLesson($question, $lessonOrders);
            }

            $aiJob->update([
                'status' => 'done',
                'questions_generated' => count($questions),
                'prompt_tokens' => $ai->lastInputTokens,
                'result_tokens' => $ai->lastOutputTokens,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $aiJob->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            throw $e;
        }
    }

    /**
     * @return array<int, int>
     */
    private function resolveLessonOrdersForSkill(): array
    {
        $lessons = Lesson::query()
            ->where('published', true)
            ->whereHas('node', function ($nodeQuery): void {
                $nodeQuery
                    ->where('published', true)
                    ->whereJsonContains('skill_ids', $this->skillId);
            })
            ->get(['id']);

        $orders = [];

        foreach ($lessons as $lesson) {
            $orders[$lesson->id] = (int) ($lesson->questions()->max('lesson_questions.order') ?? 0);
        }

        return $orders;
    }

    /**
     * @param  array<int, int>  $lessonOrders
     */
    private function attachQuestionToLesson(Question $question, array &$lessonOrders): void
    {
        if ($lessonOrders === []) {
            return;
        }

        $targetLessonId = null;
        $lowestOrder = PHP_INT_MAX;

        foreach ($lessonOrders as $lessonId => $currentOrder) {
            if ($currentOrder < $lowestOrder) {
                $lowestOrder = $currentOrder;
                $targetLessonId = $lessonId;
            }
        }

        if (! $targetLessonId) {
            return;
        }

        $nextOrder = $lessonOrders[$targetLessonId] + 1;

        $question->lessons()->attach($targetLessonId, ['order' => $nextOrder]);
        $lessonOrders[$targetLessonId] = $nextOrder;
    }

    private function resolveAiJob(int $requestedCount): AiJob
    {
        if ($this->aiJobId) {
            $aiJob = AiJob::query()->findOrFail($this->aiJobId);
            $aiJob->update([
                'status' => 'processing',
                'model' => $this->model,
                'requested_count' => $requestedCount,
                'started_at' => now(),
            ]);

            return $aiJob->refresh();
        }

        return AiJob::query()->create([
            'type' => 'generate_questions',
            'skill_id' => $this->skillId,
            'status' => 'processing',
            'model' => $this->model,
            'requested_count' => $requestedCount,
            'started_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $question
     * @return array{type: string, difficulty: int, prompt: string, options: array<string, string>|null, correct_answer: string, explanation: string}
     */
    private function normalizeQuestionPayload(array $question): array
    {
        $allowedTypes = ['multiple_choice', 'true_false', 'fill_blank', 'order_steps', 'drag_drop', 'short_answer'];
        $type = in_array(($question['type'] ?? ''), $allowedTypes, true)
            ? (string) $question['type']
            : 'multiple_choice';

        $prompt = trim((string) ($question['prompt'] ?? ''));
        $correctAnswer = trim((string) ($question['correct_answer'] ?? ''));

        if ($prompt === '' || $correctAnswer === '') {
            throw new RuntimeException('AI payload inválido: prompt/correct_answer ausente.');
        }

        $difficulty = (int) ($question['difficulty'] ?? 3);
        $difficulty = max(1, min(5, $difficulty));

        /** @var array<string, string>|null $options */
        $options = null;
        if ($type === 'multiple_choice') {
            $rawOptions = is_array($question['options'] ?? null) ? $question['options'] : [];
            $options = [
                'A' => (string) ($rawOptions['A'] ?? 'Alternativa A'),
                'B' => (string) ($rawOptions['B'] ?? 'Alternativa B'),
                'C' => (string) ($rawOptions['C'] ?? 'Alternativa C'),
                'D' => (string) ($rawOptions['D'] ?? 'Alternativa D'),
            ];
        }

        $explanation = trim((string) ($question['explanation'] ?? ''));

        return [
            'type' => $type,
            'difficulty' => $difficulty,
            'prompt' => $prompt,
            'options' => $options,
            'correct_answer' => $correctAnswer,
            'explanation' => $explanation !== '' ? $explanation : 'Questão gerada automaticamente.',
        ];
    }
}
