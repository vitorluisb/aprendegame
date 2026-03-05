<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use Database\Factories\Domain\Gameplay\Models\AttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attempt extends Model
{
    /** @use HasFactory<AttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'question_id',
        'run_id',
        'correct',
        'time_ms',
        'given_answer',
    ];

    protected function casts(): array
    {
        return [
            'correct' => 'boolean',
            'time_ms' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(LessonRun::class, 'run_id');
    }
}
