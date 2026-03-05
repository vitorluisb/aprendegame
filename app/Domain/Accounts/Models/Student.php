<?php

namespace App\Domain\Accounts\Models;

use App\Domain\Accounts\Scopes\SchoolScope;
use App\Domain\Content\Models\Grade;
use Database\Factories\Domain\Accounts\Models\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Gameplay\Models\GemTransaction;
use App\Domain\Gameplay\Models\Streak;
use App\Domain\Gameplay\Models\StudentItem;
use App\Domain\Gameplay\Models\XpTransaction;
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

    protected $fillable = [
        'user_id',
        'school_id',
        'grade_id',
        'name',
        'birth_date',
        'avatar_url',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new SchoolScope());
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
}
