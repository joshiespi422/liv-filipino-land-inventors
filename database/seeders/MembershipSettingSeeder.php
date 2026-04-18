<?php

namespace Database\Seeders;

use App\Models\MembershipSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MembershipSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MembershipSetting::updateOrCreate(
            [
                'share_capital_amount' => 50000,
                'allowed_term_months' => [1, 3, 6, 12],
            ]
        );
    }
}
