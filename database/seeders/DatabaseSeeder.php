<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            UserTypeSeeder::class,
            ServiceSeeder::class,
            BusinessTrainingSeeder::class,
            StatusSeeder::class,
            PaymentMethodSeeder::class,
            LoanSettingSeeder::class,
            MembershipSettingSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'user_type_id' => 1
        ]);

        User::factory()->create([
            'name' => 'Juan User',
            'email' => 'juan@example.com',
            'user_type_id' => 2
        ]);

        User::factory()->create([
            'name' => 'Member One',
            'email' => 'member1@example.com',
            'user_type_id' => 3
        ]);

        User::factory()->create([
            'name' => 'Member Two',
            'email' => 'member2@example.com',
            'user_type_id' => 3
        ]);

        User::factory()->create([
            'name' => 'Member Three',
            'email' => 'member3@example.com',
            'user_type_id' => 3
        ]);

        $this->call([
            DiminishingLoanSeeder::class,
            IntellectualPropertySeeder::class
        ]);

        User::factory()->count(5)->create([
            'status_id' => 10,
        ]);
    }
}
