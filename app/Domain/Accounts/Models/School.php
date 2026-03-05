<?php

namespace App\Domain\Accounts\Models;

use Database\Factories\Domain\Accounts\Models\SchoolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'plan',
        'settings',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'active' => 'boolean',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(SchoolMember::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
