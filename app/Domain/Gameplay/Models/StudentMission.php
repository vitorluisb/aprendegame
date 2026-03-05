<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use Database\Factories\Domain\Gameplay\Models\StudentMissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMission extends Model
{
    /** @use HasFactory<StudentMissionFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'mission_id',
        'date',
        'progress',
        'completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'progress' => 'integer',
            'completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(DailyMission::class, 'mission_id');
    }
}
