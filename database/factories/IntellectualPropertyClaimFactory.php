<?php

namespace Database\Factories;

use App\Models\IntellectualPropertyClaim;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntellectualPropertyClaim>
 */
class IntellectualPropertyClaimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->paragraph(2),
        ];
    }
}
