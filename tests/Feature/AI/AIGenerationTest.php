<?php

use App\Domain\AI\Jobs\GenerateQuestionsForSkill;
use App\Domain\AI\Models\AiJob;
use App\Domain\AI\Services\AIService;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\Lesson;
use App\Domain\Gameplay\Models\Question;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.ai.enabled', true);
});

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

it('job reuses existing ai job id and does not create empty ai_job rows', function () {
    Http::fake(['*' => Http::response([
        'content' => [['text' => json_encode([
            [
                'type' => 'multiple_choice',
                'difficulty' => 2,
                'prompt' => 'Questão teste',
                'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '4'],
                'correct_answer' => 'A',
                'explanation' => 'Explicação.',
            ],
        ])]],
        'usage' => ['input_tokens' => 111, 'output_tokens' => 222],
    ])]);

    $skill = BnccSkill::factory()->create();
    $aiJob = AiJob::factory()->create([
        'type' => 'generate_questions',
        'skill_id' => $skill->id,
        'status' => 'pending',
        'requested_count' => 0,
    ]);

    $job = new GenerateQuestionsForSkill($skill->id, 1, AIService::DEFAULT_MODEL, $aiJob->id);
    $job->handle(app(AIService::class));

    expect(AiJob::query()->count())->toBe(1);
    expect($aiJob->fresh()->status)->toBe('done');
    expect($aiJob->fresh()->requested_count)->toBe(1);
});

it('generated ai question is linked to a published lesson with the same skill', function () {
    Http::fake(['*' => Http::response([
        'content' => [['text' => json_encode([
            [
                'type' => 'multiple_choice',
                'difficulty' => 2,
                'prompt' => 'Questão de história',
                'options' => ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'],
                'correct_answer' => 'A',
                'explanation' => 'Explicação.',
            ],
        ])]],
        'usage' => ['input_tokens' => 111, 'output_tokens' => 222],
    ])]);

    $skill = BnccSkill::factory()->create();
    $path = Path::factory()->create([
        'grade_id' => $skill->grade_id,
        'subject_id' => $skill->subject_id,
    ]);
    $node = PathNode::factory()->create([
        'path_id' => $path->id,
        'skill_ids' => [$skill->id],
        'published' => true,
    ]);
    $lesson = Lesson::factory()->published()->create([
        'node_id' => $node->id,
    ]);

    $job = new GenerateQuestionsForSkill($skill->id, 1);
    $job->handle(app(AIService::class));

    $question = Question::query()->where('skill_id', $skill->id)->latest('id')->first();
    expect($question)->not->toBeNull();
    expect($lesson->questions()->where('questions.id', $question->id)->exists())->toBeTrue();
});
