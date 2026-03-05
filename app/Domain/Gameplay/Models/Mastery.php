<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Content\Models\BnccSkill;
use Database\Factories\Domain\Gameplay\Models\MasteryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mastery extends Model
{
    /** @use HasFactory<MasteryFactory> */
    use HasFactory;

    protected $table = 'mastery';

    protected $fillable = [
        'student_id',
        'skill_id',
        'mastery_score',
        'last_seen_at',
        'next_review_at',
        'interval_days',
        'consecutive_correct',
    ];

    protected function casts(): array
    {
        return [
            'mastery_score' => 'integer',
            'interval_days' => 'integer',
            'consecutive_correct' => 'integer',
            'last_seen_at' => 'datetime',
            'next_review_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(BnccSkill::class, 'skill_id');
    }
}
