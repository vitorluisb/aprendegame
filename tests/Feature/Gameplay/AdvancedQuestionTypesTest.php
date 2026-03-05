<?php

use App\Domain\Gameplay\Models\LessonRun;
use App\Domain\Gameplay\Models\Question;
use App\Domain\Gameplay\Services\LessonService;

it('drag_drop answer validates correctly', function () {
    $q = Question::factory()->dragDrop()->create([
        'correct_answer' => json_encode(['item1' => 'alvo1', 'item2' => 'alvo2']),
    ]);
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $q, json_encode(['item1' => 'alvo1', 'item2' => 'alvo2']), 5000);

    expect($attempt->correct)->toBeTrue();
});

it('drag_drop answer fails when order differs', function () {
    $q = Question::factory()->dragDrop()->create([
        'correct_answer' => json_encode(['item1' => 'alvo1', 'item2' => 'alvo2']),
    ]);
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $q, json_encode(['item2' => 'alvo2', 'item1' => 'alvo1']), 5000);

    expect($attempt->correct)->toBeFalse();
});

it('order_steps answer validates correctly', function () {
    $q = Question::factory()->orderSteps()->create([
        'correct_answer' => json_encode(['passo1', 'passo2', 'passo3']),
    ]);
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $q, json_encode(['passo1', 'passo2', 'passo3']), 4000);

    expect($attempt->correct)->toBeTrue();
});

it('order_steps answer fails when order is wrong', function () {
    $q = Question::factory()->orderSteps()->create([
        'correct_answer' => json_encode(['passo1', 'passo2', 'passo3']),
    ]);
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $q, json_encode(['passo2', 'passo1', 'passo3']), 4000);

    expect($attempt->correct)->toBeFalse();
});

it('fill_blank is case insensitive', function () {
    $q = Question::factory()->fillBlank()->create(['correct_answer' => 'Fotossíntese']);
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $q, 'fotossíntese', 3000);

    expect($attempt->correct)->toBeTrue();
});

it('fill_blank trims whitespace', function () {
    $q = Question::factory()->fillBlank()->create(['correct_answer' => 'resposta']);
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $q, '  resposta  ', 3000);

    expect($attempt->correct)->toBeTrue();
});

it('unknown type returns false', function () {
    $q = Question::factory()->create(['type' => 'short_answer', 'correct_answer' => 'qualquer']);
    $run = LessonRun::factory()->create();
    $service = app(LessonService::class);

    $attempt = $service->answer($run, $q, 'qualquer', 2000);

    expect($attempt->correct)->toBeFalse();
});
