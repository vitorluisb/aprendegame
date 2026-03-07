<?php

namespace Database\Factories\Domain\QuizMestre\Models;

use App\Domain\QuizMestre\Models\GkSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GkSession> */
class GkSessionFactory extends Factory
{
    protected $model = GkSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'score' => 0,
            'current_round' => 1,
            'correct_answers' => 0,
            'started_at' => now(),
            'finished_at' => null,
            'reward_xp' => 0,
            'reward_gems' => 0,
            'status' => 'active',
        ];
    }
}
