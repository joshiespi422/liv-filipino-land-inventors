<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserType;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['id' => UserType::SUPER_ADMIN, 'name' => 'super_admin'],
            ['id' => UserType::ADMIN, 'name' => 'admin'],
            ['id' => UserType::MEMBER, 'name' => 'member'],
            ['id' => UserType::BASIC, 'name' => 'basic'],
        ];

        foreach ($types as $type) {
            UserType::updateOrCreate(['id' => $type['id']], $type);
        }
    }
}
