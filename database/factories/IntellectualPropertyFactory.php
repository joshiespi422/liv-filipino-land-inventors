<?php

namespace Database\Factories;

use App\Models\IntellectualProperty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IntellectualProperty>
 */
class IntellectualPropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'applicability' => fake()->sentence(),
            'amount' => fake()->numberBetween(1000, 5000),
            'term_months' => fake()->numberBetween(6, 24),
            'creation_type' => fake()->randomElement(['business_idea', 'invention']),
            'form_type' => fake()->randomElement(['payment', 'grant']),
            'status_id' => 5,
        ];
    }
}
