<?php

namespace App\Domain\AI\Models;

use App\Domain\Accounts\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'role',
        'content',
        'blocked',
        'blocked_reason',
        'prompt_tokens',
        'result_tokens',
    ];

    protected function casts(): array
    {
        return [
            'blocked' => 'boolean',
            'prompt_tokens' => 'integer',
            'result_tokens' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
