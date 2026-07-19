<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuranDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basePath = storage_path('app/quran_data');
        $metadataPath = $basePath . '/metadata';
        $editionsPath = $basePath . '/editions_data';

        if (!file_exists($metadataPath . '/surahs.json')) {
            $this->command->error('Data not found! Please run `php artisan quran:fetch-data` first.');
            return;
        }

        // 1. Seed Surahs
        $this->command->info('Seeding Surahs...');
        $surahs = json_decode(file_get_contents($metadataPath . '/surahs.json'), true);
        $surahData = [];
        foreach ($surahs as $surah) {
            $surahData[] = [
                'number' => $surah['number'],
                'name' => $surah['name'],
                'english_name' => $surah['englishName'],
                'english_name_translation' => $surah['englishNameTranslation'],
                'revelation_type' => $surah['revelationType'],
                'number_of_ayahs' => $surah['numberOfAyahs'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        \Illuminate\Support\Facades\DB::table('surahs')->upsert($surahData, ['number'], ['name', 'english_name', 'english_name_translation', 'revelation_type', 'number_of_ayahs']);

        // 2. Seed Editions
        $this->command->info('Seeding Editions...');
        $editions = json_decode(file_get_contents($metadataPath . '/editions.json'), true);
        $editionData = [];
        // The unique key is identifier but we need edition_id for foreign keys to match `editions(id)`. 
        // Wait, the API doesn't provide integer ID for editions. Let's just insert them, we'll look up IDs later or just use them as they get created.
        foreach ($editions as $edition) {
            $editionData[] = [
                'identifier' => $edition['identifier'],
                'language' => $edition['language'],
                'name' => $edition['name'],
                'english_name' => $edition['englishName'],
                'format' => $edition['format'],
                'type' => $edition['type'],
                'direction' => $edition['direction'] ?? 'ltr',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        // Chunk to avoid too many bindings
        foreach (array_chunk($editionData, 200) as $chunk) {
            \Illuminate\Support\Facades\DB::table('editions')->upsert($chunk, ['identifier'], ['language', 'name', 'english_name', 'format', 'type', 'direction']);
        }
        
        // Cache edition IDs mapping: ['bn.bengali' => 1, 'ar.alafasy' => 2, ...]
        $editionMap = \Illuminate\Support\Facades\DB::table('editions')->pluck('id', 'identifier')->toArray();

        // 3. Seed Ayahs structure (Use quran-uthmani.json if available, else find any quran text)
        $this->command->info('Seeding Ayahs structure...');
        $baseEditionFile = $editionsPath . '/quran-uthmani.json';
        if (!file_exists($baseEditionFile)) {
            // Find any file in the folder to get structure
            $files = glob($editionsPath . '/*.json');
            if (count($files) > 0) {
                $baseEditionFile = $files[0];
            } else {
                $this->command->error('No edition data found to build ayahs structure.');
                return;
            }
        }

        $baseQuran = json_decode(file_get_contents($baseEditionFile), true);
        $ayahData = [];
        foreach ($baseQuran['surahs'] as $surah) {
            foreach ($surah['ayahs'] as $ayah) {
                $ayahData[] = [
                    'number' => $ayah['number'],
                    'surah_id' => $surah['number'],
                    'number_in_surah' => $ayah['numberInSurah'],
                    'juz' => $ayah['juz'],
                    'page' => $ayah['page'],
                    'ruku' => $ayah['ruku'],
                    'hizb_quarter' => $ayah['hizbQuarter'],
                    'sajda' => is_array($ayah['sajda']) ? true : (bool) $ayah['sajda'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        // Insert ayahs structure in chunks
        $this->command->info('Inserting Ayahs...');
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \Illuminate\Support\Facades\DB::table('ayahs')->truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        foreach (array_chunk($ayahData, 500) as $chunk) {
            \Illuminate\Support\Facades\DB::table('ayahs')->upsert($chunk, ['number'], ['surah_id', 'number_in_surah', 'juz', 'page', 'ruku', 'hizb_quarter', 'sajda']);
        }

        // 4. Seed Ayah Editions (Text and Audio)
        $this->command->info('Seeding Ayah Editions (This may take a while)...');
        $files = glob($editionsPath . '/*.json');
        
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \Illuminate\Support\Facades\DB::table('ayah_editions')->truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $bar = $this->command->getOutput()->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $identifier = basename($file, '.json');
            if (!isset($editionMap[$identifier])) continue;

            $editionId = $editionMap[$identifier];
            $quranData = json_decode(file_get_contents($file), true);
            $ayahEditionsToInsert = [];

            foreach ($quranData['surahs'] as $surah) {
                foreach ($surah['ayahs'] as $ayah) {
                    $ayahEditionsToInsert[] = [
                        'ayah_id' => $ayah['number'],
                        'edition_id' => $editionId,
                        'text' => $ayah['text'] ?? null,
                        'audio_url' => $ayah['audio'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Chunk insert
            foreach (array_chunk($ayahEditionsToInsert, 1000) as $chunk) {
                \Illuminate\Support\Facades\DB::table('ayah_editions')->insert($chunk);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nSeeding Completed Successfully!");
    }
}
