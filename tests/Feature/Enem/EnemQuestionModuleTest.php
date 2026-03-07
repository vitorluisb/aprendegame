<?php

use App\Domain\Enem\Models\EnemQuestion;
use App\Domain\Gameplay\Models\Question;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('stores enem questions in separated table from regular questions', function () {
    EnemQuestion::factory()->create([
        'area' => 'matematica',
        'subject' => 'Matemática',
    ]);

    expect(EnemQuestion::query()->count())->toBe(1);
    expect(Question::query()->count())->toBe(0);
});

it('generates enem code automatically by area prefix', function () {
    $first = EnemQuestion::factory()->create([
        'area' => 'matematica',
        'subject' => 'Matemática',
        'enem_code' => null,
    ]);

    $second = EnemQuestion::factory()->create([
        'area' => 'matematica',
        'subject' => 'Matemática',
        'enem_code' => null,
    ]);

    expect($first->enem_code)->toBe('ENEM-MAT-0001');
    expect($second->enem_code)->toBe('ENEM-MAT-0002');
});

it('renders enem question in dedicated ui with context and accepts answer', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);

    $question = EnemQuestion::factory()->create([
        'area' => 'humanas',
        'subject' => 'História',
        'status' => 'approved',
        'is_active' => true,
        'correct_option' => 'C',
    ]);

    $this->actingAs($user)
        ->get("/enem/questoes/{$question->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Enem/Play')
            ->where('question.id', $question->id)
            ->where('question.enem_code', $question->enem_code)
            ->where('question.area', 'humanas')
            ->where('question.difficulty', $question->difficulty)
            ->has('question.options', 5)
        );

    $this->actingAs($user)
        ->postJson("/enem/questoes/{$question->id}/responder", [
            'selected_option' => 'C',
        ])
        ->assertOk()
        ->assertJsonPath('correct', true)
        ->assertJsonPath('correct_option', 'C');
});
