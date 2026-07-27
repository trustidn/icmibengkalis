<?php

namespace Database\Factories;

use App\Enums\ExpertiseClaimStatus;
use App\Enums\ExpertiseLevel;
use App\Models\ExpertiseField;
use App\Models\Member;
use App\Models\MemberExpertise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberExpertise>
 */
class MemberExpertiseFactory extends Factory
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
            'expertise_field_id' => ExpertiseField::factory(),
            'level' => ExpertiseLevel::Menengah,
            'status' => ExpertiseClaimStatus::Diajukan,
        ];
    }
}
