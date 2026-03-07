<?php

namespace App\Domain\AI\Services;

use App\Domain\AI\Prompts\GenerateQuestionsPrompt;
use App\Domain\AI\Prompts\GenerateQuizMestreQuestionsPrompt;
use App\Domain\Content\Models\BnccSkill;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AIService
{
    public const DEFAULT_MODEL = 'google/gemini-3.1-flash-lite-preview';

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
        $this->ensureEnabled();

        if (blank($this->key) && ! app()->environment('testing')) {
            throw new RuntimeException('AI API key não configurada.');
        }

        $prompt = GenerateQuestionsPrompt::build($skill, $count);
        $resolvedModel = $this->normalizeModel($model);
        $text = $this->requestAiResponseText($prompt, $resolvedModel);
        $questions = $this->decodeQuestions($text);

        if (! is_array($questions)) {
            throw new RuntimeException('AI response is not valid JSON array');
        }

        return $questions;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function generateQuizMestreQuestions(string $categoryName, string $difficulty, int $count, string $model): array
    {
        $this->ensureEnabled();

        if (blank($this->key) && ! app()->environment('testing')) {
            throw new RuntimeException('AI API key não configurada.');
        }

        $prompt = GenerateQuizMestreQuestionsPrompt::build($categoryName, $difficulty, $count);
        $resolvedModel = $this->normalizeModel($model);
        $text = $this->requestAiResponseText($prompt, $resolvedModel);
        $questions = $this->decodeQuestions($text);

        if (! is_array($questions)) {
            throw new RuntimeException('AI response is not valid JSON array');
        }

        return $questions;
    }

    public function requestRawJsonResponse(string $prompt, string $model): string
    {
        $this->ensureEnabled();

        if (blank($this->key) && ! app()->environment('testing')) {
            throw new RuntimeException('AI API key não configurada.');
        }

        $resolvedModel = $this->normalizeModel($model);

        return $this->requestAiResponseText($prompt, $resolvedModel);
    }

    private function requestAiResponseText(string $prompt, string $resolvedModel): string
    {
        $client = Http::connectTimeout(10)
            ->timeout(45)
            ->retry(3, 1000);

        if ($this->isOpenRouter()) {
            $response = $client
                ->withHeaders([
                    'Authorization' => "Bearer {$this->key}",
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url, [
                    'model' => $resolvedModel,
                    'max_tokens' => 4096,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);
        } else {
            $response = $client
                ->withHeaders([
                    'x-api-key' => $this->key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post($this->url, [
                    'model' => $resolvedModel,
                    'max_tokens' => 4096,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);
        }

        if (! $response->successful()) {
            throw new RuntimeException("AI API error: HTTP {$response->status()}");
        }

        $body = $response->json();

        if ($this->isOpenRouter()) {
            $this->lastInputTokens = (int) ($body['usage']['prompt_tokens'] ?? $body['usage']['input_tokens'] ?? 0);
            $this->lastOutputTokens = (int) ($body['usage']['completion_tokens'] ?? $body['usage']['output_tokens'] ?? 0);
            $text = $this->extractOpenRouterContent($body);

            if ($text === '') {
                $text = (string) ($body['content'][0]['text'] ?? '');
            }
        } else {
            $this->lastInputTokens = (int) ($body['usage']['input_tokens'] ?? 0);
            $this->lastOutputTokens = (int) ($body['usage']['output_tokens'] ?? 0);
            $text = (string) ($body['content'][0]['text'] ?? '');
        }

        return $text;
    }

    private function ensureEnabled(): void
    {
        if (! (bool) config('services.ai.enabled', true)) {
            throw new RuntimeException('IA desativada no ambiente (AI_ENABLED=false).');
        }
    }

    private function isOpenRouter(): bool
    {
        return str_contains(strtolower($this->url), 'openrouter.ai');
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function extractOpenRouterContent(array $body): string
    {
        $content = $body['choices'][0]['message']['content'] ?? '';

        if (is_string($content)) {
            return $content;
        }

        if (! is_array($content)) {
            return '';
        }

        $textParts = collect($content)
            ->map(fn ($part) => is_array($part) ? ($part['text'] ?? '') : '')
            ->filter(fn ($part) => is_string($part) && $part !== '')
            ->values()
            ->all();

        return implode("\n", $textParts);
    }

    private function normalizeModel(string $model): string
    {
        $normalizedModel = trim($model);
        $normalizedAlias = strtolower($normalizedModel);

        return match ($normalizedAlias) {
            'google: gemini 3.1 flash lite preview',
            'gemini 3.1 flash lite preview',
            'gemini-3.1-flash-lite-preview' => self::DEFAULT_MODEL,
            default => $normalizedModel,
        };
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function decodeQuestions(string $text): ?array
    {
        /** @var mixed $decoded */
        $decoded = json_decode($text, true);

        if (is_array($decoded) && array_is_list($decoded)) {
            return $decoded;
        }

        if (is_array($decoded) && isset($decoded['questions']) && is_array($decoded['questions'])) {
            return $decoded['questions'];
        }

        $jsonArrayCandidate = $this->extractJsonArrayCandidate($text);

        if ($jsonArrayCandidate === null) {
            return null;
        }

        /** @var mixed $decodedCandidate */
        $decodedCandidate = json_decode($jsonArrayCandidate, true);

        return is_array($decodedCandidate) && array_is_list($decodedCandidate)
            ? $decodedCandidate
            : null;
    }

    private function extractJsonArrayCandidate(string $text): ?string
    {
        if (preg_match('/```(?:json)?\s*(\[[\s\S]*?\])\s*```/i', $text, $matches) === 1) {
            return trim((string) ($matches[1] ?? ''));
        }

        $start = strpos($text, '[');
        $end = strrpos($text, ']');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return trim(substr($text, $start, $end - $start + 1));
    }
}
