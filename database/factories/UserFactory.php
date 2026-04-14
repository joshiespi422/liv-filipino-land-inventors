<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,

            'user_type_id' => 4,
            'status_id' => 1,
            'phone' => fake()->unique()->phoneNumber(),
            'phone_verified_at' => now(),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other', 'Prefer not to say']),
            'birthdate' => fake()->dateTimeBetween('-17 years', 'now')->format('Y-m-d'),
            'region' => fake()->state(),
            'province' => fake()->city(),
            'city' => fake()->city(),
            'barangay' => fake()->streetName(),
            'street' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'avatar' => 'avatars/default.png',
            'valid_id_type' => fake()->randomElement(['National ID', 'Passport', 'Driver License']),
            'valid_id_number' => fake()->unique()->numerify('##########'),
            'front_valid_id_picture' => 'valid_ids/front_sample.jpg',
            'back_valid_id_picture' => 'valid_ids/back_sample.jpg',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
