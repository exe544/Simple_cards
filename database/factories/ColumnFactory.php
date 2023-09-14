<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Column>
 */
class ColumnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique(true)->text(5) . time(),
            'place' => $this->faker->unique(true)->numberBetween(1, 8),
            'board_id' => Board::factory()->create()->id,
        ];
    }

}
