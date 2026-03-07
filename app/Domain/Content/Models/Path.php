<?php

namespace App\Domain\Content\Models;

use Database\Factories\Domain\Content\Models\PathFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Path extends Model
{
    /** @use HasFactory<PathFactory> */
    use HasFactory;

    /** @var string[] */
    public const TYPES = ['regular', 'enem'];

    protected $fillable = [
        'grade_id',
        'subject_id',
        'title',
        'bimester',
        'description',
        'bncc_skills',
        'xp_total',
        'unlocks_after_path_id',
        'order',
        'published',
        'path_type',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'bncc_skills' => 'array',
            'bimester' => 'integer',
            'xp_total' => 'integer',
            'order' => 'integer',
        ];
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('path_type', $type);
    }

    public function scopeEnem(Builder $query): Builder
    {
        return $query->where('path_type', 'enem');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(PathNode::class)->orderBy('order');
    }

    public function unlocksAfterPath(): BelongsTo
    {
        return $this->belongsTo(self::class, 'unlocks_after_path_id');
    }

    public function dependentPaths(): HasMany
    {
        return $this->hasMany(self::class, 'unlocks_after_path_id');
    }

    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentPathProgress::class);
    }
}
