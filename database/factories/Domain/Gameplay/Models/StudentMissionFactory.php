<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\DailyMission;
use App\Domain\Gameplay\Models\StudentMission;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StudentMission> */
class StudentMissionFactory extends Factory
{
    protected $model = StudentMission::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'mission_id' => DailyMission::factory(),
            'date' => today(),
            'progress' => 0,
            'completed' => false,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'progress' => fn(array $attrs) => DailyMission::find($attrs['mission_id'])?->target_value ?? 1,
            'completed' => true,
            'completed_at' => now(),
        ]);
    }
}
