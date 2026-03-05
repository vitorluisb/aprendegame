<?php

namespace Database\Factories\Domain\Accounts\Models;

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\SchoolMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SchoolMember> */
class SchoolMemberFactory extends Factory
{
    protected $model = SchoolMember::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'school_id' => School::factory(),
            'role' => fake()->randomElement(['teacher', 'school_admin']),
            'invited_at' => now()->subDay(),
            'accepted_at' => now(),
            'invited_by_email' => fake()->safeEmail(),
        ];
    }
}
