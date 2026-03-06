<?php

namespace App\Domain\AI\Services;

use App\Domain\AI\Prompts\GenerateQuestionsPrompt;
use App\Domain\Content\Models\BnccSkill;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AIService
{
    public int $lastInputTokens = 0;

    public int $lastOutputTokens = 0;

    private string $url;

    private string $key;

    public function __construct()
    {
        $this->url = config('services.ai.url', 'https://api.anthropic.com/v1/messages');
        $this->key = config('services.ai.key', '');
    }

    /**
     * @return array{type: string, difficulty: int, prompt: string, options: array<string, string>|null, correct_answer: string, explanation: string}[]
     */
    public function generateQuestions(BnccSkill $skill, int $count, string $model): array
    {
        if (blank($this->key) && ! app()->environment('testing')) {
            throw new RuntimeException('AI API key não configurada.');
        }

        $prompt = GenerateQuestionsPrompt::build($skill, $count);

        $response = Http::connectTimeout(10)
            ->timeout(45)
            ->retry(3, 1000)
            ->withHeaders([
                'x-api-key' => $this->key,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->url, [
                'model' => $model,
                'max_tokens' => 4096,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException("AI API error: HTTP {$response->status()}");
        }

        $body = $response->json();

        $this->lastInputTokens = (int) ($body['usage']['input_tokens'] ?? 0);
        $this->lastOutputTokens = (int) ($body['usage']['output_tokens'] ?? 0);

        $text = $body['content'][0]['text'] ?? '';
        $questions = json_decode($text, true);

        if (! is_array($questions)) {
            throw new RuntimeException('AI response is not valid JSON array');
        }

        return $questions;
    }
}
