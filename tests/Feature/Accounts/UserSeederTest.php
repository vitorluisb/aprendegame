<?php

use App\Domain\Accounts\Enums\UserRole;
use App\Models\User;
use Database\Seeders\SchoolSeeder;
use Database\Seeders\UserSeeder;

it('is idempotent and does not duplicate fixed admin emails', function () {
    $this->seed(SchoolSeeder::class);

    $this->seed(UserSeeder::class);
    $this->seed(UserSeeder::class);

    expect(User::query()->where('email', 'admin@aprende.test')->count())->toBe(1);
    expect(User::query()->where('email', 'super@platform.com')->count())->toBe(1);

    $schools = \App\Domain\Accounts\Models\School::query()->orderBy('id')->get();

    foreach ($schools as $school) {
        expect(User::query()->where('email', 'admin-'.$school->id.'@escola.com')->count())->toBe(1);
        expect(
            User::query()
                ->where('school_id', $school->id)
                ->where('role', UserRole::Teacher->value)
                ->count()
        )->toBe(3);
        expect(
            User::query()
                ->where('school_id', $school->id)
                ->where('role', UserRole::Student->value)
                ->count()
        )->toBe(5);
    }
});
