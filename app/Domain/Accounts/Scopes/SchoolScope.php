<?php

namespace App\Domain\Accounts\Scopes;

use App\Domain\Accounts\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        if ($user->role === UserRole::SuperAdmin->value) {
            return;
        }

        $builder->where($model->getTable().'.school_id', $user->school_id);
    }
}
