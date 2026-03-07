<?php

namespace App\Domain\QuizMestre\Models;

use Database\Factories\Domain\QuizMestre\Models\GkSessionAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GkSessionAnswer extends Model
{
    /** @use HasFactory<GkSessionAnswerFactory> */
    use HasFactory;

    protected $table = 'gk_session_answers';

    protected $fillable = [
        'session_id',
        'question_id',
        'selected_option',
        'is_correct',
        'response_time_ms',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'response_time_ms' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(GkSession::class, 'session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(GkQuestion::class, 'question_id');
    }
}
