<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use Database\Factories\Domain\Gameplay\Models\StudentBadgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentBadge extends Model
{
    /** @use HasFactory<StudentBadgeFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'badge_id',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
