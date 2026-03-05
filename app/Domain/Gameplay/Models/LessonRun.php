<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use Database\Factories\Domain\Gameplay\Models\LessonRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonRun extends Model
{
    /** @use HasFactory<LessonRunFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'lesson_id',
        'started_at',
        'finished_at',
        'score',
        'xp_earned',
        'correct_count',
        'total_count',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'score' => 'integer',
            'xp_earned' => 'integer',
            'correct_count' => 'integer',
            'total_count' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class, 'run_id');
    }
}
