<?php

namespace Database\Factories;

use App\Models\IdCardEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdCardEvent>
 */
class IdCardEventFactory extends Factory
{
    protected $model = IdCardEvent::class;

    public function definition(): array
    {
        return [
            'name' => 'Pelantikan Pengurus '.fake()->year(),
            'event_date' => fake()->dateTimeBetween('now', '+2 months'),
            'is_active' => true,
        ];
    }
}
