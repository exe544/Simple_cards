<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Card;
use App\Models\Column;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $columnId = Column::first()->id ?? Column::factory()->create()->id;
        $creatorId = User::first()->id ?? User::factory()->create()->id;
        return [
            'title' => $this->faker->unique()->word . time(),
            'description' => $this->faker->paragraph(1),
            'priority' => random_int(1, 5),
            'due_dat' => $this->faker->dateTimeBetween('today', '+2 months')->format('Y-m-d'),
            'is_active' => $this->faker->boolean,
            'column_id' => $columnId,
            'creator_id' => $creatorId,
        ];
    }

    public function withTags(): static
    {
        return $this->afterCreating(function (Card $card) {
            $tags = Tag::factory(2)->create();
            $card->tags()->attach($tags);
        });
    }
}
