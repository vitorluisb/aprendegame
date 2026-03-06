<?php

namespace Database\Factories\Domain\Gameplay\Models;

use App\Domain\Gameplay\Models\ShopItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ShopItem> */
class ShopItemFactory extends Factory
{
    protected $model = ShopItem::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'type' => fake()->randomElement(ShopItem::TYPES),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'image_url' => null,
            'gem_price' => fake()->numberBetween(10, 500),
            'active' => true,
            'metadata' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }

    public function avatar(): static
    {
        return $this->state(['type' => ShopItem::TYPE_AVATAR]);
    }

    public function cheap(): static
    {
        return $this->state(['gem_price' => 10]);
    }

    public function expensive(): static
    {
        return $this->state(['gem_price' => 9999]);
    }
}
