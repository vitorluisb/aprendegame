<?php

namespace Database\Factories\Domain\Accounts\Models;

use App\Domain\Accounts\Models\School;
use App\Domain\Accounts\Models\SchoolClass;
use App\Domain\Content\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SchoolClass> */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'grade_id' => Grade::factory(),
            'name' => 'Turma '.fake()->randomElement(['A', 'B', 'C']),
            'year' => now()->year,
            'active' => true,
        ];
    }
}
