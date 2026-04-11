<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LoanSetting;

class LoanSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LoanSetting::updateOrCreate(
            ['user_id' => null],
            [
                'label' => 'Global Default Settings',
                'default_amount' => 16000.00,
                'default_interest_rate' => 0.0300,
                'default_term_months' => 12,
            ]
        );
    }
}
