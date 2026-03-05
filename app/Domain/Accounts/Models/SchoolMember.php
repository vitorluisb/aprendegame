<?php

namespace App\Domain\Accounts\Models;

use Database\Factories\Domain\Accounts\Models\SchoolMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolMember extends Model
{
    /** @use HasFactory<SchoolMemberFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_id',
        'role',
        'invited_at',
        'accepted_at',
        'invited_by_email',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
