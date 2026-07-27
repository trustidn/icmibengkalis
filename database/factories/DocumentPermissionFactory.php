<?php

namespace Database\Factories;

use App\Enums\DocumentAbility;
use App\Enums\DocumentGranteeType;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentPermission>
 */
class DocumentPermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'grantee_type' => DocumentGranteeType::User,
            'ability' => DocumentAbility::View,
        ];
    }
}
