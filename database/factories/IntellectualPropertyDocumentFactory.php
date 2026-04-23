<?php

namespace Database\Factories;

use App\Models\IntellectualPropertyDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntellectualPropertyDocument>
 */
class IntellectualPropertyDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attachment' => 'intellectual-property-documents/' . fake()->uuid() . '.pdf',
        ];
    }
}
