<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\NewsCategory;

class NewsCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Sports',
            'Politics',
            'Business',
            'News',
            'Lifestyle',
            'Entertainment',
            'Technology',
            'International News',
            'Travel',
            'AGRI-NEWS',
        ];

        foreach ($categories as $category) {
            NewsCategory::updateOrCreate(
                [
                    'slug' => Str::slug($category),
                ],
                [
                    'name' => $category,
                    'is_active' => true,
                    'description' => null,
                ]
            );
        }
    }
}
