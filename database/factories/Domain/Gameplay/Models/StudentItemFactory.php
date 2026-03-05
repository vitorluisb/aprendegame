<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Accounts\Models\Student;
use App\Domain\Gameplay\Models\ShopItem;
use App\Domain\Gameplay\Models\StudentItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StudentItem> */
class StudentItemFactory extends Factory
{
    protected $model = StudentItem::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'item_id' => ShopItem::factory(),
            'equipped' => false,
            'purchased_at' => now(),
        ];
    }

    public function equipped(): static
    {
        return $this->state(['equipped' => true]);
    }
}
