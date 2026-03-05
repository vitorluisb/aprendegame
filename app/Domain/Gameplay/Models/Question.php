<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Content\Models\BnccSkill;
use Database\Factories\Domain\Gameplay\Models\QuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'skill_id',
        'type',
        'difficulty',
        'prompt',
        'options',
        'correct_answer',
        'explanation',
        'status',
        'ai_generated',
        'avg_time_ms',
        'error_rate',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'ai_generated' => 'boolean',
            'difficulty' => 'integer',
            'avg_time_ms' => 'integer',
            'error_rate' => 'float',
        ];
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(BnccSkill::class, 'skill_id');
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_questions')
            ->withPivot('order')
            ->withTimestamps();
    }
}
