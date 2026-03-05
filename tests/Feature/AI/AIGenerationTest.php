<?php

use App\Domain\AI\Jobs\GenerateQuestionsForSkill;
use App\Domain\AI\Models\AiJob;
use App\Domain\AI\Services\AIService;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('dispatching job is queued', function () {
    Queue::fake();

    $skill = BnccSkill::factory()->create();
    GenerateQuestionsForSkill::dispatch($skill->id, 10);

    Queue::assertPushed(GenerateQuestionsForSkill::class);
});

it('job creates questions as draft and records token usage', function () {
    Http::fake(['*' => Http::response([
        'content' => [['text' => json_encode([
            [
                'type' => 'multiple_choice',
                'difficulty' => 2,
                'prompt' => 'Qual é o resultado de 2+2?',
                'options' => ['A' => '3', 'B' => '4', 'C' => '5', 'D' => '6'],
                'correct_answer' => 'B',
                'explanation' => 'Dois mais dois é igual a quatro.',
            ],
        ])]],
        'usage' => ['input_tokens' => 500, 'output_tokens' => 300],
    ])]);

    $skill = BnccSkill::factory()->create();
    $job = new GenerateQuestionsForSkill($skill->id, 1);
    $job->handle(app(AIService::class));

    $aiJob = AiJob::latest()->first();
    expect($aiJob->status)->toBe('done');
    expect($aiJob->questions_generated)->toBe(1);
    expect($aiJob->prompt_tokens)->toBe(500);
    expect($aiJob->result_tokens)->toBe(300);

    expect(Question::where('skill_id', $skill->id)->where('status', 'draft')->count())->toBe(1);
    expect(Question::where('skill_id', $skill->id)->where('ai_generated', true)->count())->toBe(1);
});

it('job marks status as failed on api error', function () {
    Http::fake(['*' => Http::response([], 500)]);

    $skill = BnccSkill::factory()->create();

    try {
        $job = new GenerateQuestionsForSkill($skill->id, 1);
        $job->handle(app(AIService::class));
    } catch (\Exception $e) {
    }

    $aiJob = AiJob::latest()->first();
    expect($aiJob->status)->toBe('failed');
    expect($aiJob->error)->not->toBeNull();
});

it('generated questions are not published automatically', function () {
    Http::fake(['*' => Http::response([
        'content' => [['text' => json_encode([
            [
                'type' => 'multiple_choice',
                'difficulty' => 3,
                'prompt' => 'Pergunta de teste?',
                'options' => ['A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd'],
                'correct_answer' => 'A',
                'explanation' => 'Explicação.',
            ],
        ])]],
        'usage' => ['input_tokens' => 100, 'output_tokens' => 50],
    ])]);

    $skill = BnccSkill::factory()->create();
    $job = new GenerateQuestionsForSkill($skill->id, 1);
    $job->handle(app(AIService::class));

    $question = Question::where('skill_id', $skill->id)->first();
    expect($question->status)->toBe('draft');
    expect($question->ai_generated)->toBeTrue();
});

it('ai job is set to processing before api call', function () {
    Http::fake(['*' => Http::response([
        'content' => [['text' => json_encode([])]],
        'usage' => ['input_tokens' => 0, 'output_tokens' => 0],
    ])]);

    $skill = BnccSkill::factory()->create();

    try {
        $job = new GenerateQuestionsForSkill($skill->id, 0);
        $job->handle(app(AIService::class));
    } catch (\Exception) {
    }

    expect(AiJob::latest()->first())->not->toBeNull();
});
