<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\Badge;
use App\Domain\Gameplay\Models\StudentBadge;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StudentBadge> */
class StudentBadgeFactory extends Factory
{
    protected $model = StudentBadge::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'badge_id' => Badge::factory(),
            'earned_at' => now(),
        ];
    }
}
