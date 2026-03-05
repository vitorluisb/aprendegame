<?php

namespace App\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use Database\Factories\Domain\Gameplay\Models\LeagueSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueSnapshot extends Model
{
    /** @use HasFactory<LeagueSnapshotFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_id',
        'class_id',
        'league',
        'weekly_xp',
        'rank_position',
        'week',
        'year',
    ];

    protected function casts(): array
    {
        return [
            'weekly_xp' => 'integer',
            'rank_position' => 'integer',
            'week' => 'integer',
            'year' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
