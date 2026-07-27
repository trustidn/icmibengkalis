<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\OrgAssignment;
use App\Models\OrgUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrgAssignment>
 */
class OrgAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'org_unit_id' => OrgUnit::factory(),
            'member_id' => Member::factory(),
            'position_title' => $this->faker->jobTitle(),
            'sort_order' => 0,
            'show_contact' => false,
        ];
    }
}
