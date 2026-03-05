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
    public const TYPES = ['regular', 'enem', 'vestibular_fuvest', 'vestibular_unicamp'];

    protected $fillable = [
        'grade_id',
        'subject_id',
        'title',
        'published',
        'path_type',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
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

    public function scopeVestibular(Builder $query): Builder
    {
        return $query->whereIn('path_type', ['vestibular_fuvest', 'vestibular_unicamp']);
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
}
