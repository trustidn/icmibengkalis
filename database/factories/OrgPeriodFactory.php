<?php

namespace Database\Factories;

use App\Models\OrgPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrgPeriod>
 */
class OrgPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->year().'-'.($this->faker->year() + 5),
            'starts_at' => now()->startOfYear(),
            'ends_at' => now()->addYears(5)->endOfYear(),
            'is_active' => false,
        ];
    }
}
