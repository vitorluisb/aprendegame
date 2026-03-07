<?php

namespace App\Domain\AI\Prompts;

use App\Domain\Content\Models\BnccSkill;

class GenerateQuestionsPrompt
{
    public static function build(BnccSkill $skill, int $count): string
    {
        $grade = $skill->grade->name;
        $ageRange = match ($skill->grade->stage) {
            'fundamental_1' => '8–10 anos',
            'fundamental_2' => '11–14 anos',
            'fundamental' => $skill->grade->order <= 5 ? '8–10 anos' : '11–14 anos',
            'medio' => '15–18 anos',
            default => '10–16 anos',
        };
        $isEarlyFundamental = self::isEarlyFundamental($skill);
        $difficultyDistribution = self::buildDifficultyDistribution($count, $isEarlyFundamental);
        $answerDistribution = self::buildAnswerDistribution($count);
        $extraRules = $isEarlyFundamental
            ? "6. Enunciado curto: até 12 palavras\n7. Alternativas curtas: até 5 palavras\n8. Vocabulário simples e contexto infantil do dia a dia\n9. Evite negativas confusas (ex.: \"NÃO é\")"
            : '6. Equilíbrio entre raciocínio, interpretação e aplicação prática';

        return <<<PROMPT
        Você é um especialista em educação brasileira e BNCC.

        Gere {$count} questões de múltipla escolha para:
        - Série: {$grade}
        - Faixa etária: {$ageRange}
        - Disciplina: {$skill->subject->name}
        - Habilidade BNCC: {$skill->code} — {$skill->description}

        REGRAS OBRIGATÓRIAS:
        1. Linguagem adequada para {$ageRange}
        2. Sem pegadinhas ou ambiguidade
        3. 4 alternativas (A, B, C, D)
        4. Explicação clara do erro em 1–2 frases
        5. Dificuldade variada: {$difficultyDistribution}
        6. Não repetir enunciados, contexto ou números entre questões; cada questão deve ser claramente diferente das demais
        7. Não usar respostas óbvias ou infantis demais; cada alternativa errada deve ser plausível
        8. Distribuição do gabarito: {$answerDistribution}
        {$extraRules}

        Responda APENAS em JSON válido no formato:
        [{"type":"multiple_choice","difficulty":2,"prompt":"...","options":{"A":"...","B":"...","C":"...","D":"..."},"correct_answer":"A","explanation":"..."}]
        PROMPT;
    }

    private static function isEarlyFundamental(BnccSkill $skill): bool
    {
        if (in_array($skill->grade->code, ['EF03', 'EF04', 'EF05'], true)) {
            return true;
        }

        return $skill->grade->stage === 'fundamental_1'
            || ($skill->grade->stage === 'fundamental' && $skill->grade->order <= 3);
    }

    private static function buildDifficultyDistribution(int $count, bool $isEarlyFundamental): string
    {
        if ($count === 30) {
            return 'exatamente 10 fáceis (1-2), 15 médias (3) e 5 difíceis (4-5)';
        }

        if ($isEarlyFundamental) {
            return '70% fácil (1-2), 25% médio (3), 5% desafiador (4)';
        }

        return '30% fácil (1-2), 50% médio (3), 20% difícil (4-5)';
    }

    private static function buildAnswerDistribution(int $count): string
    {
        if ($count === 30) {
            return 'equilibrada entre A, B, C e D (aprox. A=8, B=8, C=7, D=7), sem concentrar em A/B';
        }

        return 'equilibrada entre A, B, C e D (nenhuma letra acima de 35% do total)';
    }
}
