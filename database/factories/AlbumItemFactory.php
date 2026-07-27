<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\AlbumItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlbumItem>
 */
class AlbumItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'album_id' => Album::factory(),
            'caption' => $this->faker->sentence(4),
            'sort_order' => 0,
        ];
    }
}
