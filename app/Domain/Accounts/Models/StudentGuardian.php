<?php

namespace App\Domain\Accounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGuardian extends Model
{
    protected $fillable = [
        'guardian_user_id',
        'student_id',
        'relationship',
        'consent_given',
        'consent_given_at',
    ];

    protected function casts(): array
    {
        return [
            'consent_given' => 'boolean',
            'consent_given_at' => 'datetime',
        ];
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'guardian_user_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
