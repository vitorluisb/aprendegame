<?php

namespace App\Domain\AI\Jobs;

use App\Domain\AI\Models\AiJob;
use App\Domain\AI\Services\AIService;
use App\Domain\Content\Models\BnccSkill;
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
        private readonly string $model = 'claude-sonnet-4-6',
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

        try {
            $questions = $ai->generateQuestions($skill, $requestedCount, $this->model);

            foreach ($questions as $q) {
                $payload = $this->normalizeQuestionPayload($q);

                Question::create([
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
