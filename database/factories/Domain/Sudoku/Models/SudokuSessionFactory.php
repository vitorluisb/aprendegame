<?php

namespace Database\Factories\Domain\Sudoku\Models;

use App\Domain\Sudoku\Models\SudokuPuzzle;
use App\Domain\Sudoku\Models\SudokuSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SudokuSession> */
class SudokuSessionFactory extends Factory
{
    protected $model = SudokuSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'puzzle_id' => SudokuPuzzle::factory(),
            'status' => 'active',
            'started_at' => now(),
            'finished_at' => null,
            'elapsed_seconds' => 0,
            'mistakes_count' => 0,
            'hints_used' => 0,
            'reward_xp' => 0,
            'reward_gems' => 0,
        ];
    }
}
