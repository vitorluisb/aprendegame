<?php

namespace App\Models;

use App\Domain\Accounts\Enums\UserRole;
use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\SchoolMember;
use App\Domain\Accounts\Models\Student;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'school_id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'provider',
        'provider_id',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['super_admin', 'school_admin']);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolMemberships(): HasMany
    {
        return $this->hasMany(SchoolMember::class);
    }

    public function studentProfile(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function guardiansOf(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardians', 'guardian_user_id', 'student_id')
            ->withPivot(['relationship', 'consent_given', 'consent_given_at'])
            ->withTimestamps();
    }

    /** Alias semântico para uso em contexto de responsável */
    public function studentsGuarded(): BelongsToMany
    {
        return $this->guardiansOf();
    }

    public function ensureStudentProfile(): Student
    {
        return $this->studentProfile()
            ->withoutGlobalScopes()
            ->firstOrCreate(
                ['user_id' => $this->id],
                [
                    'school_id' => $this->school_id,
                    'grade_id' => null,
                    'name' => $this->name,
                    'avatar_url' => $this->avatar_url,
                ],
            );
    }

    public function shouldHaveStudentProfile(): bool
    {
        return $this->role === UserRole::Student->value;
    }
}
