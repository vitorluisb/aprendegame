<?php

use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\XpTransaction;
use App\Domain\Sudoku\Models\SudokuPuzzle;
use App\Domain\Sudoku\Models\SudokuSession;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('student can start a sudoku session and open play screen', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);

    SudokuPuzzle::factory()->create([
        'difficulty' => 'easy',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post('/jogos/sudoku/sessoes', ['difficulty' => 'easy'])
        ->assertRedirect();

    $session = SudokuSession::query()->latest('id')->first();

    expect($session)->not->toBeNull();
    expect($session?->status)->toBe('active');

    $this->actingAs($user)
        ->get("/jogos/sudoku/sessoes/{$session->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sudoku/Play')
            ->where('session.id', $session->id)
            ->where('session.difficulty', 'easy')
            ->has('puzzle.filled_cells', 9)
        );
});

it('sudoku move validation tracks mistakes', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);

    $puzzle = SudokuPuzzle::factory()->create([
        'difficulty' => 'easy',
        'is_active' => true,
        'puzzle_string' => '530070000600195000098000060800060003400803001700020006060000280000419005000080079',
        'solution_string' => '534678912672195348198342567859761423426853791713924856961537284287419635345286179',
    ]);

    $session = SudokuSession::factory()->create([
        'user_id' => $user->id,
        'puzzle_id' => $puzzle->id,
        'status' => 'active',
        'started_at' => now()->subMinutes(5),
    ]);

    $this->actingAs($user)
        ->postJson("/jogos/sudoku/sessoes/{$session->id}/movimentos", [
            'row_index' => 0,
            'col_index' => 2,
            'value' => 4,
        ])
        ->assertOk()
        ->assertJsonPath('result.correct', true)
        ->assertJsonPath('result.completed', false);

    $this->actingAs($user)
        ->postJson("/jogos/sudoku/sessoes/{$session->id}/movimentos", [
            'row_index' => 0,
            'col_index' => 3,
            'value' => 1,
        ])
        ->assertOk()
        ->assertJsonPath('result.correct', false);

    $session->refresh();

    expect($session->mistakes_count)->toBe(1);
    expect($session->status)->toBe('active');
});

it('completes sudoku session and creates rewards', function () {
    $user = User::factory()->create(['role' => 'student', 'school_id' => null]);

    $solution = '534678912672195348198342567859761423426853791713924856961537284287419635345286179';
    $puzzle = substr($solution, 0, 80).'0';

    $sudokuPuzzle = SudokuPuzzle::factory()->create([
        'difficulty' => 'hard',
        'is_active' => true,
        'puzzle_string' => $puzzle,
        'solution_string' => $solution,
    ]);

    $session = SudokuSession::factory()->create([
        'user_id' => $user->id,
        'puzzle_id' => $sudokuPuzzle->id,
        'status' => 'active',
        'started_at' => now()->subMinutes(3),
        'mistakes_count' => 0,
    ]);

    $this->actingAs($user)
        ->postJson("/jogos/sudoku/sessoes/{$session->id}/movimentos", [
            'row_index' => 8,
            'col_index' => 8,
            'value' => 9,
        ])
        ->assertOk()
        ->assertJsonPath('result.correct', true)
        ->assertJsonPath('result.completed', true)
        ->assertJsonPath('redirect', route('sudoku.result', ['session' => $session->id]));

    $session->refresh();

    expect($session->status)->toBe('completed');
    expect($session->reward_xp)->toBeGreaterThan(0);
    expect($session->reward_gems)->toBeGreaterThan(0);

    expect(XpTransaction::query()->where('reference_type', SudokuSession::class)->where('reference_id', $session->id)->exists())->toBeTrue();
    expect(GemTransaction::query()->where('sourceable_type', SudokuSession::class)->where('sourceable_id', $session->id)->exists())->toBeTrue();
});
