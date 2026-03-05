<?php

namespace App\Domain\Accounts\Models;

use Database\Factories\Domain\Accounts\Models\AssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'class_id',
        'teacher_id',
        'type',
        'title',
        'description',
        'due_at',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'config' => 'array',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'teacher_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssignmentItem::class);
    }
}
