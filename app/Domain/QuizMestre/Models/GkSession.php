<?php

namespace App\Domain\QuizMestre\Models;

use App\Models\User;
use Database\Factories\Domain\QuizMestre\Models\GkSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GkSession extends Model
{
    /** @use HasFactory<GkSessionFactory> */
    use HasFactory;

    protected $table = 'gk_sessions';

    protected $fillable = [
        'user_id',
        'score',
        'current_round',
        'correct_answers',
        'started_at',
        'finished_at',
        'reward_xp',
        'reward_gems',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'current_round' => 'integer',
            'correct_answers' => 'integer',
            'reward_xp' => 'integer',
            'reward_gems' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(GkSessionAnswer::class, 'session_id');
    }
}
