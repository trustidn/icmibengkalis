<?php

namespace Database\Factories;

use App\Models\Poster;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Poster>
 */
class PosterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'is_active' => true,
        ];
    }
}
