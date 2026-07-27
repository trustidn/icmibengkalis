<?php

namespace Database\Factories;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Support\NiaGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nia' => NiaGenerator::generate(),
            'full_name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['L', 'P']),
            'address' => $this->faker->address(),
            'institution' => $this->faker->company(),
            'status' => MemberStatus::Aktif,
            'joined_at' => now(),
            'show_contact_public' => false,
        ];
    }
}
