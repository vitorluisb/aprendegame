<?php

namespace App\Domain\Gameplay\Models;

use Database\Factories\Domain\Gameplay\Models\LessonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    protected $fillable = [
        'node_id',
        'title',
        'lesson_type',
        'order',
        'interaction_count',
        'difficulty',
        'xp_reward',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'interaction_count' => 'integer',
            'difficulty' => 'integer',
            'order' => 'integer',
            'xp_reward' => 'integer',
        ];
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(\App\Domain\Content\Models\PathNode::class, 'node_id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'lesson_questions')
            ->withPivot('order')
            ->orderByPivot('order')
            ->withTimestamps();
    }

    public function lessonRuns(): HasMany
    {
        return $this->hasMany(LessonRun::class);
    }
}
