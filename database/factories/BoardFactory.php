<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Board>
 */
class BoardFactory extends Factory
{

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'creator_id' => User::factory()->create()->id,
        ];
    }

    public function withBackgroundPath(): Factory
    {
        $file = UploadedFile::fake()->image('back2.jpg')->size(354);
        $path = Storage::putFileAs(
            'public/backgrounds/' . 1,
            $file,
            $file->getClientOriginalName()
        );
        return $this->state(function (array $attributes) use ($path) {
            return [
                'background_img_path' => $path,
            ];
        });
    }

    public function withBackgroundFile(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'background_img' => UploadedFile::fake()->image('back1.jpg')->size(354),
            ];
        });
    }

    public function withUsers(): static
    {
        return $this->afterCreating(function (Board $board) {
            $users = User::factory(rand(1, 5))->create();
            $board->users()->attach($users);
        });
    }
}
