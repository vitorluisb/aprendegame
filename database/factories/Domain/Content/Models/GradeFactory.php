<?php

namespace Database\Factories\Domain\Content\Models;

use App\Domain\Content\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Grade> */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    protected static int $orderCounter = 0;

    public function definition(): array
    {
        static $order = 0;
        $order++;

        $stage = fake()->randomElement(['fundamental', 'medio']);
        $stageCode = $stage === 'fundamental' ? 'EF' : 'EM';
        $year = fake()->unique()->numberBetween(1, 9);

        return [
            'name' => "{$year}º Ano ".($stage === 'fundamental' ? 'EF' : 'EM'),
            'code' => "{$year}{$stageCode}",
            'stage' => $stage,
            'order' => $order,
        ];
    }
}
