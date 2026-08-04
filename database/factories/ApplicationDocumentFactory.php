<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationDocument>
 */
class ApplicationDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            'birth_certificate',
            'family_card',
            'parent_id',
            'report_cards',
        ]);

        return [
            'application_id' => Application::factory(),
            'document_type' => $type,
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'file_name' => $type.'.pdf',
            'file_size' => fake()->numberBetween(100000, 5000000),
            'is_required' => $type !== 'report_cards',
        ];
    }
}
