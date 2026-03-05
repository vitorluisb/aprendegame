<?php

namespace App\Domain\Content\Models;

use Database\Factories\Domain\Content\Models\SubjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
    ];

    public function skills(): HasMany
    {
        return $this->hasMany(BnccSkill::class);
    }
}
