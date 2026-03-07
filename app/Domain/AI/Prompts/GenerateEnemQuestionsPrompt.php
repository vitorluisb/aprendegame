<?php

namespace App\Domain\AI\Prompts;

class GenerateEnemQuestionsPrompt
{
    public static function build(string $area, string $subject, int $count, string $difficulty, ?int $yearReference = null): string
    {
        $yearInstruction = $yearReference ? "- Referência temporal preferencial: {$yearReference}." : '- Use referências contemporâneas compatíveis com Ensino Médio.';
        $yearJsonValue = $yearReference !== null ? (string) $yearReference : 'null';

        return <<<PROMPT
        Você é especialista em itens do ENEM.

        Gere {$count} questões no formato ENEM para:
        - Área: {$area}
        - Componente: {$subject}
        - Dificuldade: {$difficulty}
        {$yearInstruction}

        REGRAS OBRIGATÓRIAS:
        1. Cada questão deve ter texto contextual de 3 a 8 linhas.
        2. O enunciado deve depender do contexto.
        3. Exatamente 5 alternativas (A, B, C, D, E).
        4. Apenas 1 alternativa correta.
        5. Explicação objetiva da alternativa correta.
        6. Linguagem acadêmica clara para Ensino Médio.
        7. Evite perguntas triviais e respostas óbvias.
        8. Não repita questões no mesmo lote.

        Responda APENAS em JSON válido no formato:
        [
          {
            "area": "{$area}",
            "subject": "{$subject}",
            "context_text": "...",
            "question_text": "...",
            "option_a": "...",
            "option_b": "...",
            "option_c": "...",
            "option_d": "...",
            "option_e": "...",
            "correct_option": "A",
            "difficulty": "{$difficulty}",
            "year_reference": {$yearJsonValue},
            "explanation": "..."
          }
        ]
        PROMPT;
    }
}
