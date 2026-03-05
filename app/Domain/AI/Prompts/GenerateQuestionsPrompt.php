<?php

namespace App\Domain\AI\Prompts;

use App\Domain\Content\Models\BnccSkill;

class GenerateQuestionsPrompt
{
    public static function build(BnccSkill $skill, int $count): string
    {
        $grade = $skill->grade->name;
        $ageRange = match ($skill->grade->stage) {
            'fundamental' => $skill->grade->order <= 5 ? '8–10 anos' : '11–14 anos',
            'medio' => '15–18 anos',
            default => '10–16 anos',
        };

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
        5. Dificuldade variada: 30% fácil (1-2), 50% médio (3), 20% difícil (4-5)

        Responda APENAS em JSON válido no formato:
        [{"type":"multiple_choice","difficulty":2,"prompt":"...","options":{"A":"...","B":"...","C":"...","D":"..."},"correct_answer":"A","explanation":"..."}]
        PROMPT;
    }
}
