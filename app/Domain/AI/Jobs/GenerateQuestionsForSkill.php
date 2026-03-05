<?php

namespace App\Domain\AI\Jobs;

use App\Domain\AI\Models\AiJob;
use App\Domain\AI\Services\AIService;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateQuestionsForSkill implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 2;

    public function __construct(
        private readonly int $skillId,
        private readonly int $count = 10,
        private readonly string $model = 'claude-sonnet-4-6',
    ) {
        $this->onQueue('ai');
    }

    public function handle(AIService $ai): void
    {
        $skill = BnccSkill::with(['grade', 'subject'])->findOrFail($this->skillId);

        $aiJob = AiJob::create([
            'type' => 'generate_questions',
            'skill_id' => $this->skillId,
            'status' => 'processing',
            'model' => $this->model,
            'started_at' => now(),
        ]);

        try {
            $questions = $ai->generateQuestions($skill, $this->count, $this->model);

            foreach ($questions as $q) {
                Question::create([
                    'skill_id' => $this->skillId,
                    'type' => $q['type'],
                    'difficulty' => $q['difficulty'],
                    'prompt' => $q['prompt'],
                    'options' => $q['options'] ?? null,
                    'correct_answer' => $q['correct_answer'],
                    'explanation' => $q['explanation'],
                    'status' => 'draft', // curadoria manual antes de publicar
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
        } catch (\Exception $e) {
            $aiJob->update(['status' => 'failed', 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
