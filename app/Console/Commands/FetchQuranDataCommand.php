<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchQuranDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quran:fetch-data {--limit= : Limit the number of editions to fetch for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Quran data from api.alquran.cloud and save as JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Quran data sync...');
        
        $basePath = storage_path('app/quran_data');
        $metadataPath = $basePath . '/metadata';
        $editionsPath = $basePath . '/editions_data';
        
        // Ensure directories exist
        if (!is_dir($metadataPath)) mkdir($metadataPath, 0777, true);
        if (!is_dir($editionsPath)) mkdir($editionsPath, 0777, true);

        // 1. Fetch Surahs
        $this->info('Fetching Surahs metadata...');
        $surahsJson = Http::get('https://api.alquran.cloud/v1/surah')->json();
        file_put_contents($metadataPath . '/surahs.json', json_encode($surahsJson['data'], JSON_PRETTY_PRINT));
        $this->info('Surahs saved.');

        // 2. Fetch Editions
        $this->info('Fetching Editions metadata...');
        $editionsJson = Http::get('https://api.alquran.cloud/v1/edition')->json();
        $editions = $editionsJson['data'];
        file_put_contents($metadataPath . '/editions.json', json_encode($editions, JSON_PRETTY_PRINT));
        $this->info('Editions saved. Total editions found: ' . count($editions));

        $limit = $this->option('limit');
        if ($limit) {
            $editions = array_slice($editions, 0, (int)$limit);
            $this->info("Limiting to $limit editions for testing.");
        }

        // 3. Fetch each edition data
        $bar = $this->output->createProgressBar(count($editions));
        $bar->start();

        foreach ($editions as $edition) {
            $identifier = $edition['identifier'];
            $filePath = $editionsPath . '/' . $identifier . '.json';

            if (file_exists($filePath)) {
                $bar->advance();
                continue; // Skip if already downloaded
            }

            try {
                $response = Http::get('https://api.alquran.cloud/v1/quran/' . $identifier);
                if ($response->successful()) {
                    file_put_contents($filePath, json_encode($response->json()['data'], JSON_PRETTY_PRINT));
                } else {
                    $this->error("\nFailed to fetch edition: $identifier");
                }
            } catch (\Exception $e) {
                $this->error("\nException on edition $identifier: " . $e->getMessage());
            }

            // Rate limiting protection
            sleep(1);
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nQuran data sync completed successfully!");
    }
}
