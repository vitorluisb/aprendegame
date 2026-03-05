<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use Database\Factories\Domain\Gameplay\Models\StreakFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Streak extends Model
{
    /** @use HasFactory<StreakFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'current',
        'best',
        'last_activity_date',
        'freeze_used_at',
    ];

    protected function casts(): array
    {
        return [
            'current' => 'integer',
            'best' => 'integer',
            'last_activity_date' => 'date',
            'freeze_used_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
