<?php

namespace App\Domain\AI\Prompts;

class GenerateQuizMestreQuestionsPrompt
{
    public static function build(string $categoryName, string $difficulty, int $count): string
    {
        $difficultyLabel = match ($difficulty) {
            'easy' => 'fácil',
            'medium' => 'médio',
            'hard' => 'difícil',
            default => 'médio',
        };

        return <<<PROMPT
        Você é um especialista em criação de perguntas para quiz de cultura geral.

        Gere {$count} perguntas para o jogo Quiz Mestre.
        - Categoria: {$categoryName}
        - Dificuldade: {$difficultyLabel}
        - Idioma: português do Brasil

        REGRAS OBRIGATÓRIAS:
        1. Cada pergunta deve ter 4 alternativas objetivas (A, B, C, D)
        2. Apenas uma alternativa correta
        3. Evite ambiguidade e pegadinhas
        4. Explicação curta da resposta correta (1–2 frases)
        5. Conteúdo apropriado para estudantes (sem temas impróprios)

        Responda APENAS em JSON válido no formato:
        [{"question_text":"...","options":{"A":"...","B":"...","C":"...","D":"..."},"correct_option":"A","explanation":"..."}]
        PROMPT;
    }
}
