<?php

use App\Domain\AI\Services\AIService;
use App\Domain\QuizMestre\Models\GkCategory;
use App\Domain\QuizMestre\Models\GkQuestion;
use App\Domain\QuizMestre\Services\QuizMestreQuestionGenerationService;

use function Pest\Laravel\mock;

it('generates quiz mestre questions via ai with keyed options', function () {
    $category = GkCategory::factory()->create(['name' => 'História']);

    $ai = mock(AIService::class);
    $ai->shouldReceive('generateQuizMestreQuestions')
        ->once()
        ->andReturn([
            [
                'question_text' => 'Quem descobriu o Brasil?',
                'options' => [
                    'A' => 'Pedro Alvares Cabral',
                    'B' => 'Dom Pedro I',
                    'C' => 'Tiradentes',
                    'D' => 'Getúlio Vargas',
                ],
                'correct_option' => 'A',
                'explanation' => 'Cabral chegou ao território em 1500.',
            ],
            [
                'prompt' => 'Em que ano foi a Proclamação da República?',
                'options' => [
                    'A' => '1822',
                    'B' => '1889',
                    'C' => '1930',
                    'D' => '1964',
                ],
                'correct_answer' => '1889',
                'explanation' => 'A Proclamação ocorreu em 15 de novembro de 1889.',
            ],
        ]);

    $service = app(QuizMestreQuestionGenerationService::class);
    $created = $service->generateForCategory($category, 'medium', 2);

    expect($created)->toBe(2);
    expect(GkQuestion::query()->count())->toBe(2);
    expect(GkQuestion::query()->where('source_reference', 'ai:gemini')->count())->toBe(2);

    $question = GkQuestion::query()->latest('id')->firstOrFail();
    expect($question->correct_option)->toBe('B');
    expect($question->option_b)->toBe('1889');
});

it('throws when ai returns no valid quiz mestre questions', function () {
    $category = GkCategory::factory()->create(['name' => 'Geografia']);

    $ai = mock(AIService::class);
    $ai->shouldReceive('generateQuizMestreQuestions')
        ->once()
        ->andReturn([
            [
                'question_text' => '',
                'options' => ['A' => '', 'B' => '', 'C' => '', 'D' => ''],
                'correct_option' => 'A',
            ],
        ]);

    $service = app(QuizMestreQuestionGenerationService::class);

    expect(fn () => $service->generateForCategory($category, 'easy', 1))
        ->toThrow(\RuntimeException::class, 'A IA não retornou questões válidas para o Quiz Mestre.');
});
