<?php

use App\Domain\AI\Services\AIService;
use App\Domain\Enem\Models\EnemQuestion;
use App\Domain\Enem\Services\EnemQuestionGenerationService;

use function Pest\Laravel\mock;

it('stores enem questions when ai returns wrapped questions payload with options map', function () {
    $ai = mock(AIService::class);
    $ai->shouldReceive('requestRawJsonResponse')
        ->once()
        ->andReturn(json_encode([
            'questions' => [
                [
                    'area' => 'humanas',
                    'subject' => 'História',
                    'context' => 'Texto de apoio da questão.',
                    'question' => 'Qual alternativa está correta?',
                    'options' => [
                        'A' => 'Alternativa A',
                        'B' => 'Alternativa B',
                        'C' => 'Alternativa C',
                        'D' => 'Alternativa D',
                        'E' => 'Alternativa E',
                    ],
                    'correct_answer' => 'Alternativa C',
                    'difficulty' => 'médio',
                    'year' => 2021,
                    'explicacao' => 'Explicação da resposta.',
                ],
            ],
        ], JSON_THROW_ON_ERROR));

    $service = app(EnemQuestionGenerationService::class);
    $created = $service->generateAndStore('humanas', 'História', 1, 'medium', null, AIService::DEFAULT_MODEL);

    expect($created)->toHaveCount(1);
    expect(EnemQuestion::query()->count())->toBe(1);
    expect($created[0]->correct_option)->toBe('C');
    expect($created[0]->option_c)->toBe('Alternativa C');
    expect($created[0]->difficulty)->toBe('medium');
    expect($created[0]->year_reference)->toBe(2021);
});

it('stores enem questions when ai returns markdown json with trailing commas and option fields', function () {
    $ai = mock(AIService::class);
    $ai->shouldReceive('requestRawJsonResponse')
        ->once()
        ->andReturn(<<<'JSON'
```json
[
  {
    "area": "matemática",
    "subject": "Matemática",
    "context_text": "Contexto de matemática.",
    "question_text": "Quanto é 2 + 2?",
    "option_a": "1",
    "option_b": "2",
    "option_c": "3",
    "option_d": "4",
    "option_e": "5",
    "correct_option": "D",
    "difficulty": "hard",
    "year_reference": 2022,
    "explanation": "Soma direta.",
  },
]
```
JSON);

    $service = app(EnemQuestionGenerationService::class);
    $created = $service->generateAndStore('matematica', 'Matemática', 1, 'hard', null, AIService::DEFAULT_MODEL);

    expect($created)->toHaveCount(1);
    expect($created[0]->area)->toBe('matematica');
    expect($created[0]->correct_option)->toBe('D');
    expect($created[0]->option_d)->toBe('4');
});
