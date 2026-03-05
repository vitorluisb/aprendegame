<?php

namespace App\Domain\Content\Models;

use App\Domain\Gameplay\Models\Lesson;
use Database\Factories\Domain\Content\Models\PathNodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PathNode extends Model
{
    /** @use HasFactory<PathNodeFactory> */
    use HasFactory;

    protected $fillable = [
        'path_id',
        'order',
        'title',
        'node_type',
        'skill_ids',
        'published',
    ];

    protected function casts(): array
    {
        return [
            'skill_ids' => 'array',
            'published' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'node_id');
    }
}
