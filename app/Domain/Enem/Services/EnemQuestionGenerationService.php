<?php

namespace App\Domain\Enem\Services;

use App\Domain\AI\Prompts\GenerateEnemQuestionsPrompt;
use App\Domain\AI\Services\AIService;
use App\Domain\Enem\Models\EnemQuestion;
use RuntimeException;

class EnemQuestionGenerationService
{
    public function __construct(private readonly AIService $aiService) {}

    /**
     * @return list<EnemQuestion>
     */
    public function generateAndStore(
        string $area,
        string $subject,
        int $count,
        string $difficulty,
        ?int $yearReference,
        string $model,
    ): array {
        $count = max(1, min(50, $count));

        $prompt = GenerateEnemQuestionsPrompt::build($area, $subject, $count, $difficulty, $yearReference);
        $responseText = $this->aiService->requestRawJsonResponse($prompt, $model);
        $decoded = $this->decodeAiPayload($responseText);

        $created = [];

        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }

            $normalized = $this->normalizeQuestionItem($item, $area, $subject, $difficulty, $yearReference);

            if ($normalized === null) {
                continue;
            }

            $created[] = EnemQuestion::query()->create([
                'area' => $normalized['area'],
                'subject' => $normalized['subject'],
                'context_text' => $normalized['context_text'],
                'question_text' => $normalized['question_text'],
                'option_a' => $normalized['option_a'],
                'option_b' => $normalized['option_b'],
                'option_c' => $normalized['option_c'],
                'option_d' => $normalized['option_d'],
                'option_e' => $normalized['option_e'],
                'correct_option' => $normalized['correct_option'],
                'difficulty' => $normalized['difficulty'],
                'year_reference' => $normalized['year_reference'],
                'explanation' => $normalized['explanation'],
                'status' => 'reviewed',
                'is_active' => true,
            ]);
        }

        if ($created === []) {
            throw new RuntimeException('A IA não retornou questões ENEM válidas.');
        }

        return $created;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function decodeAiPayload(string $responseText): array
    {
        $candidates = [$responseText];

        $jsonArrayCandidate = $this->extractJsonArrayCandidate($responseText);

        if ($jsonArrayCandidate !== null) {
            $candidates[] = $jsonArrayCandidate;
        }

        $jsonObjectCandidate = $this->extractJsonObjectCandidate($responseText);

        if ($jsonObjectCandidate !== null) {
            $candidates[] = $jsonObjectCandidate;
        }

        foreach (array_unique($candidates) as $candidate) {
            $decoded = $this->decodeJsonLoosely($candidate);

            if (! is_array($decoded)) {
                continue;
            }

            if (array_is_list($decoded)) {
                /** @var list<array<string, mixed>> $decoded */
                return $decoded;
            }

            foreach (['questions', 'items', 'data', 'result'] as $key) {
                $nested = $decoded[$key] ?? null;

                if (is_array($nested) && array_is_list($nested)) {
                    /** @var list<array<string, mixed>> $nested */
                    return $nested;
                }
            }

            if (isset($decoded['context_text'], $decoded['question_text'])) {
                /** @var array<string, mixed> $decoded */
                return [$decoded];
            }
        }

        throw new RuntimeException('Resposta da IA inválida para ENEM.');
    }

    /**
     * @return array<string, mixed>|list<array<string, mixed>>|null
     */
    private function decodeJsonLoosely(string $candidate): ?array
    {
        $sanitized = trim($candidate);
        $sanitized = preg_replace('/^\xEF\xBB\xBF/', '', $sanitized) ?? $sanitized;

        /** @var mixed $decoded */
        $decoded = json_decode($sanitized, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $withoutTrailingCommas = preg_replace('/,\s*([}\]])/', '$1', $sanitized) ?? $sanitized;
        /** @var mixed $decodedWithoutTrailingCommas */
        $decodedWithoutTrailingCommas = json_decode($withoutTrailingCommas, true);

        return is_array($decodedWithoutTrailingCommas) ? $decodedWithoutTrailingCommas : null;
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

    private function extractJsonObjectCandidate(string $text): ?string
    {
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/i', $text, $matches) === 1) {
            return trim((string) ($matches[1] ?? ''));
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return trim(substr($text, $start, $end - $start + 1));
    }

    private function normalizeArea(string $area): string
    {
        $area = strtolower(trim($area));
        $area = str_replace(['ã', 'á', 'â', 'ç', 'é', 'ê', 'í', 'ó', 'ô', 'ú'], ['a', 'a', 'a', 'c', 'e', 'e', 'i', 'o', 'o', 'u'], $area);

        return match (true) {
            str_contains($area, 'linguagens') => 'linguagens',
            str_contains($area, 'human') => 'humanas',
            str_contains($area, 'natureza') => 'natureza',
            str_contains($area, 'mat') => 'matematica',
            default => 'linguagens',
        };
    }

    private function normalizeDifficulty(string $difficulty): string
    {
        $difficulty = strtolower(trim($difficulty));
        $difficulty = str_replace(['á', 'ã', 'é', 'í', 'ó', 'ú'], ['a', 'a', 'e', 'i', 'o', 'u'], $difficulty);

        if (in_array($difficulty, ['facil', 'easy'], true)) {
            return 'easy';
        }

        if (in_array($difficulty, ['medio', 'médio', 'medium'], true)) {
            return 'medium';
        }

        if (in_array($difficulty, ['dificil', 'difícil', 'hard'], true)) {
            return 'hard';
        }

        return in_array($difficulty, ['easy', 'medium', 'hard'], true)
            ? $difficulty
            : 'medium';
    }

    private function normalizeOption(string $option): string
    {
        $option = strtoupper(trim($option));
        $option = preg_replace('/[^A-E]/', '', $option) ?? $option;

        return in_array($option, ['A', 'B', 'C', 'D', 'E'], true)
            ? $option
            : 'A';
    }

    private function parseOptionKeyStrict(string $value): ?string
    {
        $value = strtoupper(trim($value));

        if (preg_match('/\b([A-E])\b/', $value, $matches) === 1) {
            return (string) $matches[1];
        }

        return in_array($value, ['A', 'B', 'C', 'D', 'E'], true) ? $value : null;
    }

    /**
     * @return array{
     *     area:string,
     *     subject:string,
     *     context_text:string,
     *     question_text:string,
     *     option_a:string,
     *     option_b:string,
     *     option_c:string,
     *     option_d:string,
     *     option_e:string,
     *     correct_option:string,
     *     difficulty:string,
     *     year_reference:int|null,
     *     explanation:string
     * }|null
     */
    private function normalizeQuestionItem(
        array $item,
        string $defaultArea,
        string $defaultSubject,
        string $defaultDifficulty,
        ?int $defaultYearReference,
    ): ?array {
        $contextText = trim((string) ($item['context_text'] ?? $item['context'] ?? $item['texto_contexto'] ?? $item['texto_base'] ?? ''));
        $questionText = trim((string) ($item['question_text'] ?? $item['question'] ?? $item['prompt'] ?? $item['enunciado'] ?? $item['pergunta'] ?? ''));
        $options = $this->extractOptions($item);
        $correctOption = $this->resolveCorrectOption($item, $options);

        if (
            $contextText === ''
            || $questionText === ''
            || count($options) !== 5
            || $correctOption === null
            || $options['A'] === ''
            || $options['B'] === ''
            || $options['C'] === ''
            || $options['D'] === ''
            || $options['E'] === ''
        ) {
            return null;
        }

        $yearRaw = $item['year_reference'] ?? $item['year'] ?? $item['ano_referencia'] ?? $item['ano'] ?? null;
        $yearReference = is_numeric($yearRaw) ? (int) $yearRaw : $defaultYearReference;

        return [
            'area' => $this->normalizeArea((string) ($item['area'] ?? $defaultArea)),
            'subject' => trim((string) ($item['subject'] ?? $item['disciplina'] ?? $defaultSubject)),
            'context_text' => $contextText,
            'question_text' => $questionText,
            'option_a' => $options['A'],
            'option_b' => $options['B'],
            'option_c' => $options['C'],
            'option_d' => $options['D'],
            'option_e' => $options['E'],
            'correct_option' => $correctOption,
            'difficulty' => $this->normalizeDifficulty((string) ($item['difficulty'] ?? $item['dificuldade'] ?? $defaultDifficulty)),
            'year_reference' => $yearReference,
            'explanation' => trim((string) ($item['explanation'] ?? $item['explicacao'] ?? '')),
        ];
    }

    /**
     * @return array{A:string,B:string,C:string,D:string,E:string}
     */
    private function extractOptions(array $item): array
    {
        $direct = [
            'A' => trim((string) ($item['option_a'] ?? $item['alternativa_a'] ?? '')),
            'B' => trim((string) ($item['option_b'] ?? $item['alternativa_b'] ?? '')),
            'C' => trim((string) ($item['option_c'] ?? $item['alternativa_c'] ?? '')),
            'D' => trim((string) ($item['option_d'] ?? $item['alternativa_d'] ?? '')),
            'E' => trim((string) ($item['option_e'] ?? $item['alternativa_e'] ?? '')),
        ];

        if ($direct['A'] !== '' && $direct['B'] !== '' && $direct['C'] !== '' && $direct['D'] !== '' && $direct['E'] !== '') {
            return $direct;
        }

        $nested = $item['options'] ?? $item['alternatives'] ?? $item['alternativas'] ?? null;

        if (! is_array($nested)) {
            return $direct;
        }

        if (array_is_list($nested)) {
            $mappedList = ['A' => '', 'B' => '', 'C' => '', 'D' => '', 'E' => ''];

            foreach ($nested as $index => $option) {
                $key = chr(65 + (int) $index);
                if (! in_array($key, ['A', 'B', 'C', 'D', 'E'], true)) {
                    continue;
                }

                if (is_array($option)) {
                    $mappedList[$key] = trim((string) ($option['text'] ?? $option['value'] ?? ''));
                } else {
                    $mappedList[$key] = trim((string) $option);
                }
            }

            return $mappedList;
        }

        $mappedAssoc = ['A' => '', 'B' => '', 'C' => '', 'D' => '', 'E' => ''];

        foreach ($nested as $key => $text) {
            $normalizedKey = $this->parseOptionKeyStrict((string) $key);
            if ($normalizedKey !== null && isset($mappedAssoc[$normalizedKey])) {
                $mappedAssoc[$normalizedKey] = trim((string) $text);
            }
        }

        return $mappedAssoc;
    }

    /**
     * @param  array{A:string,B:string,C:string,D:string,E:string}  $options
     */
    private function resolveCorrectOption(array $item, array $options): ?string
    {
        $candidate = (string) ($item['correct_option'] ?? $item['correctAnswer'] ?? $item['gabarito'] ?? $item['correta'] ?? $item['correct'] ?? '');
        $normalizedCandidate = $this->parseOptionKeyStrict($candidate);

        if ($normalizedCandidate !== null) {
            return $normalizedCandidate;
        }

        $correctAnswerText = trim((string) ($item['correct_answer'] ?? $item['answer'] ?? $item['resposta_correta'] ?? ''));

        if ($correctAnswerText !== '') {
            $normalizedText = strtolower($correctAnswerText);

            foreach ($options as $key => $optionText) {
                if (strtolower(trim($optionText)) === $normalizedText) {
                    return $key;
                }
            }

            if (preg_match('/\b([A-E])\b/i', $correctAnswerText, $matches) === 1) {
                return strtoupper((string) $matches[1]);
            }
        }

        return null;
    }
}
