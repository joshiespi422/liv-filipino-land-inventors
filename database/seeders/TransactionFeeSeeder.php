<?php

namespace Database\Seeders;

use App\Models\TransactionFee;
use Illuminate\Database\Seeder;

class TransactionFeeSeeder extends Seeder
{
    public function run(): void
    {
        TransactionFee::updateOrCreate(
            ['module' => 'Transfer'],
            [
                'type' => TransactionFee::TYPE_PHP,
                'transfer_fee' => 10,
                'minimum_fee' => 50,
            ],
        );
    }
}
