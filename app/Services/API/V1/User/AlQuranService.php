<?php

namespace App\Services\API\V1\User;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlQuranService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function getAllSurahs()
    {
        try {
            return Cache::remember('all_surahs', now()->addMonth(), function () {
                $apiUrl = "https://api.alquran.cloud/v1/surah";
                $response = Http::get($apiUrl);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new Exception("Failed to fetch surahs from external API.");
                }
            });
        } catch (Exception $e) {
            Log::error('AlQuranService::getAllSurahs ' . $e->getMessage());
            throw $e;
        }
    }

    public function getSurahByNumber(string $number, $editions = "'quran-uthmani','ar.alafasy','bn.bengali'"): mixed
    {
        try {

            return Cache::remember('surah_' . $number . '_' . $editions, now()->addMonth(), function () use ($number, $editions) {
                $apiUrl = "https://api.alquran.cloud/v1/surah/" . $number . "/editions/" . $editions;
                $response = Http::get($apiUrl);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new Exception("Failed to fetch surah from external API.");
                }
            });
        } catch (Exception $e) {
            Log::error('AlQuranService::getSurahByNumber' . $e->getMessage());
            throw $e;
        }
    }

    public function getAllJuzs()
    {
        try {
            return Cache::remember('all_juzs', now()->addMonth(), function () {

                $juzs = [];

                for ($i = 1; $i <= 30; $i++) {
                    $response = Http::get("https://api.alquran.cloud/v1/juz/{$i}/quran-uthmani");

                    if (!$response->successful()) {
                        throw new Exception("Failed to fetch juz {$i}");
                    }

                    $data = $response->json('data');

                    $juzs[] = [
                        'juz' => $i,
                        'ayahs_count' => count($data['ayahs']),
                        'start_surah' => $data['ayahs'][0]['surah']['englishName'],
                        'start_ayah' => $data['ayahs'][0]['numberInSurah'],
                    ];
                }

                return $juzs;
            });
        } catch (Exception $e) {
            Log::error('AlQuranService::getAllJuzs ' . $e->getMessage());
            throw $e;
        }
    }

    public function getJuzByNumber(string $number)
    {
        try {
            return Cache::remember('juz_' . $number, now()->addMonth(), function () use ($number) {
                $apiUrl = "https://api.alquran.cloud/v1/juz/" . $number;
                $response = Http::get($apiUrl);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new Exception("Failed to fetch juz from external API.");
                }
            });
        } catch (Exception $e) {
            Log::error('AlQuranService::getJuzByNumber ' . $e->getMessage());
            throw $e;
        }
    }


    public function getAllSurahsByUserLanguage()
    {
        try {
            $edition = "bn.bengali";
            // $edition = $this->getEditionByLanguage($this->user->language_code);

            return Cache::remember(
                "surahs_{$edition}",
                now()->addMonth(),
                function () use ($edition) {

                    $response = Http::get(
                        "https://api.alquran.cloud/v1/surah",
                        ['edition' => $edition]
                    );

                    if (!$response->successful()) {
                        throw new Exception('Failed to fetch surahs');
                    }

                    return $response->json('data');
                }
            );
        } catch (Exception $e) {
            Log::error('AlQuranService::getAllSurahsByUserLanguage ' . $e->getMessage());
            throw $e;
        }
    }

    private function getEditionByLanguage($languageCode)
    {
        $editionMap = [
            'en' => 'en.sahih',
            'ar' => 'quran-uthmani',
            'bn' => 'bn.bengali',
            // add more mappings as needed
        ];

        return $editionMap[$languageCode] ?? 'en.sahih';
    }
}