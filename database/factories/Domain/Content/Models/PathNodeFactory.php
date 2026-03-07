<?php

namespace Database\Factories\Domain\Content\Models;

use App\Domain\Content\Models\Path;
use App\Domain\Content\Models\PathNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PathNode> */
class PathNodeFactory extends Factory
{
    protected $model = PathNode::class;

    protected static int $orderSequence = 1;

    public function definition(): array
    {
        $path = Path::inRandomOrder()->first() ?? Path::factory()->create();

        return [
            'path_id' => $path->id,
            'order' => fake()->unique()->numberBetween(1, 1000),
            'title' => fake()->words(3, true),
            'node_type' => fake()->randomElement(['lesson', 'review', 'bonus', 'boss']),
            'xp_reward' => fake()->numberBetween(80, 140),
            'skill_ids' => [],
            'published' => false,
        ];
    }

    public function forPath(Path $path): static
    {
        return $this->state(['path_id' => $path->id]);
    }
}
