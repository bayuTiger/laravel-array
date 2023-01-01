<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Frontend',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Backend',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Infra',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
            Category::insert($categories);
        
    }
}
