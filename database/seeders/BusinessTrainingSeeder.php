<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessTrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Leadership', 'Technical Skills', 'Soft Skills', 'Compliance', 'Marketing'];

        foreach ($types as $typeName) {
            // 1. Create Type
            $typeId = DB::table('business_training_types')->insertGetId([
                'name' => $typeName,
                'slug' => Str::slug($typeName),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            for ($c = 1; $c <= 3; $c++) {
                // 2. Create Categories (3 per type)
                $categoryName = "{$typeName} Category {$c}";
                $categoryId = DB::table('business_training_categories')->insertGetId([
                    'business_training_type_id' => $typeId,
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'description' => "Description for {$categoryName}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                for ($m = 1; $m <= 6; $m++) {
                    // 3. Create Modules (6 per category)
                    DB::table('business_trainings')->insert([
                        'business_training_category_id' => $categoryId,
                        'module' => $m,
                        'content' => json_encode([
                            [
                                'title' => "Module {$m} - Part A",
                                'description' => "This section covers the introductory concepts for {$categoryName} in module {$m}. 
                                It is designed to establish a solid foundation for all learners. 
                                Participants will explore key terminologies and basic frameworks essential for success.",
                            ],
                            [
                                'title' => "Module {$m} - Part B",
                                'description' => "Building on the previous section, this part dives deeper into advanced strategies for {$categoryName}. 
                                It includes real-world case studies and interactive exercises to reinforce learning. 
                                By the end of this section, you will be able to apply these concepts in professional environments.",
                            ]
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
