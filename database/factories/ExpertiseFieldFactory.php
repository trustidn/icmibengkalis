<?php

namespace Database\Factories;

use App\Models\ExpertiseField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpertiseField>
 */
class ExpertiseFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
            'description' => $this->faker->sentence(),
        ];
    }
}
