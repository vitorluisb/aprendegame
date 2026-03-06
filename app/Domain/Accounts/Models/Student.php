<?php

namespace App\Domain\Accounts\Models;

use App\Domain\Accounts\Scopes\SchoolScope;
use App\Domain\Content\Models\Grade;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\Streak;
use App\Domain\Gameplay\Models\StudentItem;
use App\Domain\Gameplay\Models\XpTransaction;
use Carbon\Carbon;
use Database\Factories\Domain\Accounts\Models\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    use SoftDeletes;

    public const DEFAULT_LIVES = 10;

    public const LIFE_PURCHASE_COST = 40;

    protected $fillable = [
        'user_id',
        'school_id',
        'grade_id',
        'name',
        'birth_date',
        'avatar_url',
        'lives_current',
        'lives_max',
        'lives_refilled_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'lives_current' => 'integer',
            'lives_max' => 'integer',
            'lives_refilled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new SchoolScope);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'student_guardians', 'student_id', 'guardian_user_id')
            ->withPivot(['relationship', 'consent_given', 'consent_given_at'])
            ->withTimestamps();
    }

    public function streak(): HasOne
    {
        return $this->hasOne(Streak::class);
    }

    public function xpTransactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    public function totalXp(): int
    {
        return (int) $this->xpTransactions()->sum('amount');
    }

    public function gemTransactions(): HasMany
    {
        return $this->hasMany(GemTransaction::class);
    }

    public function totalGems(): int
    {
        return (int) $this->gemTransactions()->sum('amount');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(StudentItem::class);
    }

    public function loseLife(int $amount = 1): void
    {
        if ($amount <= 0) {
            return;
        }

        $freshLives = max(0, ((int) $this->lives_current) - $amount);
        $this->update([
            'lives_current' => $freshLives,
            'lives_refilled_at' => now(),
        ]);
    }

    public function recoverLife(int $amount = 1): void
    {
        if ($amount <= 0) {
            return;
        }

        $newLives = min((int) $this->lives_max, ((int) $this->lives_current) + $amount);
        $this->update([
            'lives_current' => $newLives,
            'lives_refilled_at' => now(),
        ]);
    }

    public function hasLives(): bool
    {
        return (int) $this->lives_current > 0;
    }

    public function isAtMaxLives(): bool
    {
        return (int) $this->lives_current >= (int) $this->lives_max;
    }

    public function refillLivesIfDue(): void
    {
        if ($this->isAtMaxLives()) {
            return;
        }

        $currentLives = (int) $this->lives_current;
        $maxLives = (int) $this->lives_max;

        if ($maxLives <= $currentLives) {
            return;
        }

        $now = now();
        $reference = $this->lives_refilled_at ?? $this->updated_at ?? $this->created_at ?? $now;
        $elapsedHours = $reference->diffInHours($now);

        if ($elapsedHours < 1) {
            return;
        }

        $recoverAmount = min($maxLives - $currentLives, $elapsedHours);
        $newLives = $currentLives + $recoverAmount;

        $this->update([
            'lives_current' => $newLives,
            'lives_refilled_at' => Carbon::parse($reference)->addHours($recoverAmount),
        ]);
    }
}
