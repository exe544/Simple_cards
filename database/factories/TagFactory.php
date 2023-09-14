<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Card;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->text(30),
            'color' => $this->faker->colorName,
        ];
    }

    public function withCards(): static
    {
        return $this->afterCreating(function (Tag $tag) {
            $cards = Card::factory(3)->create();
            $tag->cards()->attach($cards);
        });
    }

}
