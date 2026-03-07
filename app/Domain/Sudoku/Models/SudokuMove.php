<?php

namespace App\Domain\Sudoku\Models;

use Database\Factories\Domain\Sudoku\Models\SudokuMoveFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SudokuMove extends Model
{
    /** @use HasFactory<SudokuMoveFactory> */
    use HasFactory;

    protected $table = 'sudoku_moves';

    protected $fillable = [
        'session_id',
        'row_index',
        'col_index',
        'value',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'row_index' => 'integer',
            'col_index' => 'integer',
            'value' => 'integer',
            'is_correct' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SudokuSession::class, 'session_id');
    }
}
