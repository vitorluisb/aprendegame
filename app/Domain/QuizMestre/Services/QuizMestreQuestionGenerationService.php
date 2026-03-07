<?php

namespace App\Domain\QuizMestre\Services;

use App\Domain\AI\Services\AIService;
use App\Domain\QuizMestre\Models\GkCategory;
use App\Domain\QuizMestre\Models\GkQuestion;
use RuntimeException;

class QuizMestreQuestionGenerationService
{
    public function __construct(private readonly AIService $aiService) {}

    public function generateForCategory(GkCategory $category, string $difficulty, int $quantity, string $model = AIService::DEFAULT_MODEL): int
    {
        $resolvedDifficulty = $this->normalizeDifficulty($difficulty);
        $resolvedQuantity = max(1, min(100, $quantity));

        $rawQuestions = $this->aiService->generateQuizMestreQuestions(
            categoryName: $category->name,
            difficulty: $resolvedDifficulty,
            count: $resolvedQuantity,
            model: $model,
        );

        $createdCount = 0;

        foreach ($rawQuestions as $rawQuestion) {
            $payload = $this->normalizePayload($rawQuestion, $resolvedDifficulty);

            if ($payload === null) {
                continue;
            }

            GkQuestion::query()->create([
                'category_id' => $category->id,
                'question_text' => $payload['question_text'],
                'option_a' => $payload['option_a'],
                'option_b' => $payload['option_b'],
                'option_c' => $payload['option_c'],
                'option_d' => $payload['option_d'],
                'correct_option' => $payload['correct_option'],
                'explanation' => $payload['explanation'],
                'difficulty' => $resolvedDifficulty,
                'age_group' => $this->ageGroupForDifficulty($resolvedDifficulty),
                'is_active' => true,
                'source_reference' => 'ai:gemini',
                'metadata_json' => [
                    'ai_generated' => true,
                    'model' => $model,
                ],
            ]);

            $createdCount++;
        }

        if ($createdCount === 0) {
            throw new RuntimeException('A IA não retornou questões válidas para o Quiz Mestre.');
        }

        return $createdCount;
    }

    private function normalizeDifficulty(string $difficulty): string
    {
        return match (strtolower(trim($difficulty))) {
            'easy' => 'easy',
            'medium' => 'medium',
            'hard' => 'hard',
            default => 'medium',
        };
    }

    private function ageGroupForDifficulty(string $difficulty): string
    {
        return match ($difficulty) {
            'easy' => '8-10',
            'medium' => '11-14',
            'hard' => '15-18',
            default => '11-14',
        };
    }

    /**
     * @param  array<string, mixed>  $rawQuestion
     * @return array{question_text:string,option_a:string,option_b:string,option_c:string,option_d:string,correct_option:string,explanation:?string}|null
     */
    private function normalizePayload(array $rawQuestion, string $difficulty): ?array
    {
        $questionText = trim((string) ($rawQuestion['question_text'] ?? $rawQuestion['prompt'] ?? ''));

        if ($questionText === '') {
            return null;
        }

        $rawOptions = $rawQuestion['options'] ?? null;

        if (! is_array($rawOptions)) {
            return null;
        }

        $optionA = trim((string) ($rawOptions['A'] ?? ''));
        $optionB = trim((string) ($rawOptions['B'] ?? ''));
        $optionC = trim((string) ($rawOptions['C'] ?? ''));
        $optionD = trim((string) ($rawOptions['D'] ?? ''));

        if ($optionA === '' || $optionB === '' || $optionC === '' || $optionD === '') {
            return null;
        }

        $correctOption = strtoupper(trim((string) ($rawQuestion['correct_option'] ?? $rawQuestion['correct_answer'] ?? '')));

        if (! in_array($correctOption, ['A', 'B', 'C', 'D'], true)) {
            $normalizedCorrectText = strtolower(trim((string) ($rawQuestion['correct_answer'] ?? '')));
            $optionMap = [
                'A' => strtolower($optionA),
                'B' => strtolower($optionB),
                'C' => strtolower($optionC),
                'D' => strtolower($optionD),
            ];

            foreach ($optionMap as $key => $text) {
                if ($text === $normalizedCorrectText && $normalizedCorrectText !== '') {
                    $correctOption = $key;
                    break;
                }
            }
        }

        if (! in_array($correctOption, ['A', 'B', 'C', 'D'], true)) {
            return null;
        }

        $explanation = trim((string) ($rawQuestion['explanation'] ?? ''));

        return [
            'question_text' => $questionText,
            'option_a' => $optionA,
            'option_b' => $optionB,
            'option_c' => $optionC,
            'option_d' => $optionD,
            'correct_option' => $correctOption,
            'explanation' => $explanation !== '' ? $explanation : "Questão {$difficulty} gerada por IA.",
        ];
    }
}
