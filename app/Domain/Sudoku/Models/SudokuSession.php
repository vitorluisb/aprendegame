<?php

namespace App\Domain\Sudoku\Models;

use App\Models\User;
use Database\Factories\Domain\Sudoku\Models\SudokuSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SudokuSession extends Model
{
    /** @use HasFactory<SudokuSessionFactory> */
    use HasFactory;

    protected $table = 'sudoku_sessions';

    protected $fillable = [
        'user_id',
        'puzzle_id',
        'status',
        'started_at',
        'finished_at',
        'elapsed_seconds',
        'mistakes_count',
        'hints_used',
        'reward_xp',
        'reward_gems',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'elapsed_seconds' => 'integer',
            'mistakes_count' => 'integer',
            'hints_used' => 'integer',
            'reward_xp' => 'integer',
            'reward_gems' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function puzzle(): BelongsTo
    {
        return $this->belongsTo(SudokuPuzzle::class, 'puzzle_id');
    }

    public function moves(): HasMany
    {
        return $this->hasMany(SudokuMove::class, 'session_id');
    }
}
