<?php

namespace App\Domain\Gameplay\Models;

use Database\Factories\Domain\Gameplay\Models\BadgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    /** @use HasFactory<BadgeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'condition_type',
        'condition_value',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'condition_value' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domain\Accounts\Models\Student::class,
            'student_badges'
        )->withPivot('earned_at')->withTimestamps();
    }
}
