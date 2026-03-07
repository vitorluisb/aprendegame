<?php

namespace App\Domain\Sudoku\Models;

use Database\Factories\Domain\Sudoku\Models\SudokuPuzzleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SudokuPuzzle extends Model
{
    /** @use HasFactory<SudokuPuzzleFactory> */
    use HasFactory;

    protected $table = 'sudoku_puzzles';

    protected $fillable = [
        'difficulty',
        'puzzle_string',
        'solution_string',
        'clues_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'clues_count' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(SudokuSession::class, 'puzzle_id');
    }
}
