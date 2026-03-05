<?php

namespace Database\Factories\Domain\Accounts\Models;

use App\Domain\Accounts\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditLog> */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['login', 'logout', 'register']),
            'target_type' => 'User',
            'target_id' => fake()->numberBetween(1, 500),
            'meta' => ['source' => 'factory'],
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => now(),
        ];
    }
}
