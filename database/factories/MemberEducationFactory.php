<?php

namespace Database\Factories;

use App\Enums\EducationLevel;
use App\Models\Member;
use App\Models\MemberEducation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberEducation>
 */
class MemberEducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'level' => EducationLevel::S1,
            'institution' => $this->faker->company(),
            'major' => $this->faker->word(),
            'graduated_year' => $this->faker->numberBetween(1990, 2024),
        ];
    }
}
