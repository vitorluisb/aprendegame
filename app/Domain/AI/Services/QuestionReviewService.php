<?php

namespace App\Domain\AI\Services;

use App\Domain\Gameplay\Models\Question;
use RuntimeException;

class QuestionReviewService
{
    public function __construct(private readonly AIService $aiService) {}

    /**
     * @param  array<int, string>  $recentPrompts
     * @return array{approved: bool, reason: string, suggested_correct_answer: string|null}
     */
    public function reviewQuestion(Question $question, string $model, array $recentPrompts = []): array
    {
        $options = is_array($question->options) ? $question->options : [];
        $optionA = (string) ($options['A'] ?? '');
        $optionB = (string) ($options['B'] ?? '');
        $optionC = (string) ($options['C'] ?? '');
        $optionD = (string) ($options['D'] ?? '');
        $recentPromptsText = $recentPrompts === []
            ? '- (sem histórico)'
            : collect($recentPrompts)
                ->take(10)
                ->map(fn (string $prompt, int $index): string => ($index + 1).'. '.$prompt)
                ->implode("\n");

        $prompt = <<<PROMPT
        Você é um revisor pedagógico rigoroso.

        Revise a questão abaixo para qualidade e adequação:
        - Série/Habilidade: {$question->skill_id}
        - Enunciado: {$question->prompt}
        - Opções: A={$optionA} | B={$optionB} | C={$optionC} | D={$optionD}
        - Gabarito atual: {$question->correct_answer}
        - Explicação atual: {$question->explanation}

        Histórico de enunciados recentes da mesma habilidade:
        {$recentPromptsText}

        Critérios obrigatórios:
        1. Reprovar se estiver muito parecida/repetida com histórico.
        2. Reprovar se estiver muito óbvia, infantilizada demais ou mal formulada.
        3. Reprovar se o gabarito estiver inconsistente com opções/enunciado.
        4. Aprovar apenas se estiver clara, correta e com bom nível para estudo.

        Responda APENAS em JSON válido no formato:
        {"approved":true,"reason":"...","suggested_correct_answer":"A"}

        Regras de saída:
        - approved: true ou false
        - reason: obrigatório e curto (até 160 caracteres)
        - suggested_correct_answer: "A"|"B"|"C"|"D" ou null
        PROMPT;

        $raw = $this->aiService->requestRawJsonResponse($prompt, $model);

        return $this->parseDecision($raw);
    }

    /**
     * @return array{approved: bool, reason: string, suggested_correct_answer: string|null}
     */
    private function parseDecision(string $raw): array
    {
        $candidate = $this->extractJsonObjectCandidate($raw);
        if ($candidate === null) {
            throw new RuntimeException('Resposta da IA revisora inválida (JSON ausente).');
        }

        /** @var mixed $decoded */
        $decoded = json_decode($candidate, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Resposta da IA revisora inválida (JSON malformado).');
        }

        $approved = (bool) ($decoded['approved'] ?? false);
        $reason = trim((string) ($decoded['reason'] ?? 'Sem justificativa.'));
        $suggested = strtoupper(trim((string) ($decoded['suggested_correct_answer'] ?? '')));
        $suggestedAnswer = in_array($suggested, ['A', 'B', 'C', 'D'], true) ? $suggested : null;

        return [
            'approved' => $approved,
            'reason' => $reason !== '' ? $reason : 'Sem justificativa.',
            'suggested_correct_answer' => $suggestedAnswer,
        ];
    }

    private function extractJsonObjectCandidate(string $text): ?string
    {
        /** @var mixed $decoded */
        $decoded = json_decode($text, true);
        if (is_array($decoded) && ! array_is_list($decoded)) {
            return json_encode($decoded, JSON_UNESCAPED_UNICODE);
        }

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
}
