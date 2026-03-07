<?php

namespace Database\Factories\Domain\Sudoku\Models;

use App\Domain\Sudoku\Models\SudokuMove;
use App\Domain\Sudoku\Models\SudokuSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SudokuMove> */
class SudokuMoveFactory extends Factory
{
    protected $model = SudokuMove::class;

    public function definition(): array
    {
        return [
            'session_id' => SudokuSession::factory(),
            'row_index' => fake()->numberBetween(0, 8),
            'col_index' => fake()->numberBetween(0, 8),
            'value' => fake()->numberBetween(1, 9),
            'is_correct' => true,
        ];
    }
}
