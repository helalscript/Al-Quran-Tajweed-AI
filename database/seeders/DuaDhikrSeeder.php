<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DuaDhikr;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DuaDhikrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basePath = public_path('dua/dua-dhikr');
        
        if (!File::exists($basePath)) {
            $this->command->error('Dua-dhikr directory not found!');
            return;
        }

        $categories = [
            'morning-dhikr' => 'Morning Dhikr',
            'evening-dhikr' => 'Evening Dhikr',
            'daily-dua' => 'Daily Dua',
            'selected-dua' => 'Selected Dua',
            'dhikr-after-salah' => 'Dhikr After Salah',
        ];

        // Use only English language as requested
        $languages = ['en'];

        foreach ($categories as $slug => $name) {
            $category = Category::where('slug', $slug)->first();
            
            if (!$category) {
                $this->command->warn("Category '{$slug}' not found. Skipping...");
                continue;
            }

            foreach ($languages as $lang) {
                $jsonPath = $basePath . '/' . $slug . '/' . $lang . '.json';
                
                if (!File::exists($jsonPath)) {
                    $this->command->warn("File not found: {$jsonPath}");
                    continue;
                }

                $duas = json_decode(File::get($jsonPath), true);

                if (!$duas || !is_array($duas)) {
                    $this->command->warn("Invalid JSON format in: {$jsonPath}");
                    continue;
                }

                $order = 1;
                foreach ($duas as $dua) {
                    DuaDhikr::updateOrCreate(
                        [
                            'category_id' => $category->id,
                            'title' => $dua['title'],
                            'language_code' => $lang,
                        ],
                        [
                            'category_id' => $category->id,
                            'title' => $dua['title'],
                            'arabic' => $dua['arabic'] ?? '',
                            'latin' => $dua['latin'] ?? '',
                            'translation' => $dua['translation'] ?? '',
                            'notes' => $dua['notes'] ?? null,
                            'benefits' => $dua['benefits'] ?? null,
                            'fawaid' => $dua['fawaid'] ?? null,
                            'source' => $dua['source'] ?? null,
                            'language_code' => $lang,
                            'order' => $order++,
                            'status' => 'active',
                        ]
                    );
                }
            }
        }

        $this->command->info('Dua Dhikrs seeded successfully!');
    }
}
