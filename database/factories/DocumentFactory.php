<?php

namespace Database\Factories;

use App\Enums\DocType;
use App\Enums\DocumentAccessLevel;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
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
            'doc_type' => DocType::Lainnya,
            'description' => $this->faker->sentence(),
            'uploaded_by' => User::factory(),
            'access_level' => DocumentAccessLevel::Anggota,
            'document_date' => now(),
            'current_version' => 1,
        ];
    }
}
