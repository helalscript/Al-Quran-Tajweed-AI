<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesPath = public_path('dua/core/categories.json');
        
        if (!File::exists($categoriesPath)) {
            $this->command->error('Categories JSON file not found!');
            return;
        }

        $categoriesData = json_decode(File::get($categoriesPath), true);

        if (!$categoriesData) {
            $this->command->error('Invalid JSON format in categories.json');
            return;
        }

        $order = 1;
        
        // Use only English categories as requested
        $categories = $categoriesData['en'] ?? [];
        
        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                [
                    'slug' => $categoryData['slug'],
                    'type' => 'dua',
                ],
                [
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'type' => 'dua',
                    'translations' => null, // store only English
                    'order' => $order++,
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('Categories seeded successfully!');
    }
}
