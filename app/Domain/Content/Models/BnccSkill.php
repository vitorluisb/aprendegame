<?php

namespace App\Domain\Content\Models;

use Database\Factories\Domain\Content\Models\BnccSkillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BnccSkill extends Model
{
    /** @use HasFactory<BnccSkillFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'grade_id',
        'subject_id',
        'thematic_unit',
        'knowledge_object',
        'version',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
