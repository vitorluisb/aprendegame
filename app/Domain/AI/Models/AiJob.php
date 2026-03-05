<?php

namespace App\Domain\AI\Models;

use App\Domain\Content\Models\BnccSkill;
use Database\Factories\Domain\AI\Models\AiJobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiJob extends Model
{
    /** @use HasFactory<AiJobFactory> */
    use HasFactory;

    protected $table = 'ai_jobs';

    protected $fillable = [
        'type',
        'skill_id',
        'status',
        'config',
        'prompt_tokens',
        'result_tokens',
        'model',
        'error',
        'questions_generated',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'prompt_tokens' => 'integer',
            'result_tokens' => 'integer',
            'questions_generated' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(BnccSkill::class, 'skill_id');
    }
}
