<?php

namespace App\Domain\Content\Models;

use App\Domain\Accounts\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPathProgress extends Model
{
    protected $table = 'student_path_progress';

    protected $fillable = [
        'student_id',
        'path_id',
        'status',
        'current_node_order',
        'xp_earned',
        'xp_total',
        'stars',
        'accuracy_percent',
        'attempts_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_node_order' => 'integer',
            'xp_earned' => 'integer',
            'xp_total' => 'integer',
            'stars' => 'integer',
            'accuracy_percent' => 'float',
            'attempts_count' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }
}
