<?php

namespace App\Domain\Gameplay\Models;

use Database\Factories\Domain\Gameplay\Models\DailyMissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyMission extends Model
{
    /** @use HasFactory<DailyMissionFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'target_value',
        'xp_reward',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'integer',
            'xp_reward' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function studentMissions(): HasMany
    {
        return $this->hasMany(StudentMission::class, 'mission_id');
    }
}
