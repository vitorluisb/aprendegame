<?php

namespace App\Domain\Accounts\Services;

use App\Domain\Accounts\Models\AuditLog as AuditLogModel;
use App\Models\User;

class AuditLog
{
    public static function record(?User $user, string $action, array $meta = []): void
    {
        AuditLogModel::create([
            'user_id' => $user?->id,
            'action' => $action,
            'meta' => $meta,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
