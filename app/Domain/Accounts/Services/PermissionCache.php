<?php

namespace App\Domain\Accounts\Services;

use App\Domain\Accounts\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionCache
{
    public static function get(User $user): array
    {
        return Cache::remember(
            self::cacheKey($user),
            now()->addMinutes(30),
            fn (): array => self::build($user),
        );
    }

    public static function flush(User $user): void
    {
        Cache::forget(self::cacheKey($user));
    }

    private static function cacheKey(User $user): string
    {
        return "permissions:{$user->id}:{$user->school_id}";
    }

    private static function build(User $user): array
    {
        $role = $user->role;

        return [
            'content.view' => in_array($role, [UserRole::Teacher->value, UserRole::SchoolAdmin->value, UserRole::SuperAdmin->value], true),
            'content.edit' => in_array($role, [UserRole::SchoolAdmin->value, UserRole::SuperAdmin->value], true),
            'students.view' => in_array($role, [UserRole::Teacher->value, UserRole::SchoolAdmin->value, UserRole::SuperAdmin->value, UserRole::Guardian->value], true),
        ];
    }
}
