<?php

namespace Database\Factories;

use App\Models\OrgPeriod;
use App\Models\OrgUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrgUnit>
 */
class OrgUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'org_period_id' => OrgPeriod::factory(),
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'sort_order' => 0,
        ];
    }
}
