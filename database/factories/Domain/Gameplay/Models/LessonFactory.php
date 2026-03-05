<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Content\Models\PathNode;
use App\Domain\Gameplay\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Lesson> */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        $node = PathNode::inRandomOrder()->first() ?? PathNode::factory()->create();

        return [
            'node_id' => $node->id,
            'title' => fake()->words(4, true),
            'interaction_count' => fake()->numberBetween(8, 12),
            'difficulty' => fake()->numberBetween(1, 5),
            'published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(['published' => true]);
    }

    public function forNode(PathNode $node): static
    {
        return $this->state(['node_id' => $node->id]);
    }
}
