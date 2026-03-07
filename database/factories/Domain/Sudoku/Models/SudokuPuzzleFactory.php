<?php

namespace Database\Factories\Domain\Sudoku\Models;

use App\Domain\Sudoku\Models\SudokuPuzzle;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SudokuPuzzle> */
class SudokuPuzzleFactory extends Factory
{
    protected $model = SudokuPuzzle::class;

    public function definition(): array
    {
        return [
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'puzzle_string' => '530070000600195000098000060800060003400803001700020006060000280000419005000080079',
            'solution_string' => '534678912672195348198342567859761423426853791713924856961537284287419635345286179',
            'clues_count' => 30,
            'is_active' => true,
        ];
    }
}
