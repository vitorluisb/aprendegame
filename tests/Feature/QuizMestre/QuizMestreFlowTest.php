<?php

use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\XpTransaction;
use App\Domain\QuizMestre\Models\GkCategory;
use App\Domain\QuizMestre\Models\GkQuestion;
use App\Domain\QuizMestre\Models\GkSession;
use App\Domain\QuizMestre\Services\QuizMestreService;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('student can start and play quiz mestre session', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $category = GkCategory::factory()->create(['name' => 'História']);

    GkQuestion::factory()->create([
        'category_id' => $category->id,
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post('/jogos/quiz-mestre/sessoes')
        ->assertRedirect();

    $session = GkSession::query()->latest('id')->first();

    expect($session)->not->toBeNull();
    expect($session?->status)->toBe('active');

    $this->actingAs($user)
        ->get("/jogos/quiz-mestre/sessoes/{$session->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('QuizMestre/Play')
            ->where('session.id', $session->id)
            ->has('question.options', 4)
        );
});

it('quiz mestre submit updates round and rewards and finalizes on wrong answer', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $category = GkCategory::factory()->create();

    $q1 = GkQuestion::factory()->create([
        'category_id' => $category->id,
        'difficulty' => 'easy',
        'correct_option' => 'A',
        'is_active' => true,
    ]);

    $q2 = GkQuestion::factory()->create([
        'category_id' => $category->id,
        'difficulty' => 'easy',
        'correct_option' => 'B',
        'is_active' => true,
    ]);

    $this->actingAs($user)->post('/jogos/quiz-mestre/sessoes');
    $session = GkSession::query()->latest('id')->firstOrFail();

    $response1 = $this->actingAs($user)
        ->postJson("/jogos/quiz-mestre/sessoes/{$session->id}/responder", [
            'question_id' => $q1->id,
            'selected_option' => 'A',
            'response_time_ms' => 1200,
        ])
        ->assertOk();

    $response1->assertJsonPath('result.is_correct', true);
    expect($session->fresh()->status)->toBe('active');
    expect($session->fresh()->score)->toBeGreaterThan(0);

    $response2 = $this->actingAs($user)
        ->postJson("/jogos/quiz-mestre/sessoes/{$session->id}/responder", [
            'question_id' => $q2->id,
            'selected_option' => 'A',
            'response_time_ms' => 1300,
        ])
        ->assertOk();

    $response2->assertJsonPath('result.is_correct', false);
    $response2->assertJsonPath('finished', true);

    $session->refresh();
    expect($session->status)->toBe('lost');
    expect($session->finished_at)->not->toBeNull();

    expect(XpTransaction::query()->where('reference_type', GkSession::class)->where('reference_id', $session->id)->exists())->toBeTrue();
    expect(GemTransaction::query()->where('sourceable_type', GkSession::class)->where('sourceable_id', $session->id)->exists())->toBeFalse();
});

it('uses 30-round difficulty progression for quiz mestre', function () {
    $service = app(QuizMestreService::class);

    expect(QuizMestreService::MAX_ROUNDS)->toBe(30);
    expect($service->difficultyForRound(1))->toBe('easy');
    expect($service->difficultyForRound(15))->toBe('easy');
    expect($service->difficultyForRound(16))->toBe('medium');
    expect($service->difficultyForRound(25))->toBe('medium');
    expect($service->difficultyForRound(26))->toBe('hard');
    expect($service->difficultyForRound(30))->toBe('hard');
});

it('quiz mestre can fallback to other difficulties before repeating recent questions', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);
    $category = GkCategory::factory()->create();

    GkQuestion::factory()->create([
        'category_id' => $category->id,
        'difficulty' => 'medium',
        'is_active' => true,
    ]);

    $this->actingAs($user)->post('/jogos/quiz-mestre/sessoes');
    $session = GkSession::query()->latest('id')->firstOrFail();

    $this->actingAs($user)
        ->get("/jogos/quiz-mestre/sessoes/{$session->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('QuizMestre/Play')
            ->where('session.id', $session->id)
            ->has('question.options', 4)
        );
});
