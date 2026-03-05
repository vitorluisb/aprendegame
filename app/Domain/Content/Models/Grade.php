<?php

namespace App\Domain\Content\Models;

use Database\Factories\Domain\Content\Models\GradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    /** @use HasFactory<GradeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'stage',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function skills(): HasMany
    {
        return $this->hasMany(BnccSkill::class);
    }
}
