<?php

namespace Database\Seeders;

use App\Models\TransactionFee;
use Illuminate\Database\Seeder;

class TransactionFeeSeeder extends Seeder
{
    public function run(): void
    {
        TransactionFee::create([
            'module' => 'Transfer',
            'type' => 'PHP',
            'value' => 10,
            'minimum_fee' => 50,
        ]);
    }
}
