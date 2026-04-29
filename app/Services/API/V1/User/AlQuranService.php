<?php

namespace App\Services\API\V1\User;

use App\Models\AppDisplaySetting;
use App\Models\Edition;
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
                $apiUrl = 'https://api.alquran.cloud/v1/surah';
                $response = Http::get($apiUrl);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new Exception('Failed to fetch surahs from external API.');
                }
            });
        } catch (Exception $e) {
            Log::error('AlQuranService::getAllSurahs ' . $e->getMessage());
            throw $e;
        }
    }

    public function getSurahByNumber(string $number, string $defaultEdition = 'quran-uthmani'): array
    {
        try {
            $userLanguageEdition = (array) $this->getUserLanguageEdition();
            $userRecitation = (array) $this->getUserRecitation();

            // Add tajweed here
            $allEditions = array_map(function ($e) {
                return trim($e, "'\" ");
            }, array_unique(array_merge(
                    [$defaultEdition],
                    $userLanguageEdition,
                    $userRecitation,
                    ['quran-tajweed'] //tajweed added
                )));

            $editionParam = implode(",", $allEditions);

            $response = Cache::remember(
                'surah_' . $number . '_' . md5($editionParam),
                now()->addMonth(),
                function () use ($number, $editionParam) {

                    $apiUrl = "https://api.alquran.cloud/v1/surah/$number/editions/$editionParam";

                    $res = Http::get($apiUrl);

                    if ($res->successful()) {
                        return $res->json();
                    }

                    throw new Exception('Failed to fetch surah from external API.');
                }
            );

            // main data array
            $editions = collect($response['data']);

            // group by identifier (easy access)
            $grouped = $editions->mapWithKeys(function ($item) {
                return [$item['edition']['identifier'] => $item];
            });

            // final structured response
            return [
                'status' => true,
                'message' => 'Surah fetched successfully',
                'code' => 200,
                'data' => [
                    'meta' => [
                        'number' => $grouped->first()['number'] ?? null,
                        'name' => $grouped->first()['name'] ?? null,
                        'englishName' => $grouped->first()['englishName'] ?? null,
                        'englishNameTranslation' => $grouped->first()['englishNameTranslation'] ?? null,
                        'revelationType' => $grouped->first()['revelationType'] ?? null,
                        'numberOfAyahs' => $grouped->first()['numberOfAyahs'] ?? null,
                    ],

                    // Arabic
                    'arabic' => $grouped['quran-uthmani']['ayahs'] ?? [],

                    // Tajweed (important)
                    'tajweed' => $grouped['quran-tajweed']['ayahs'] ?? [],

                    // Translation (dynamic)
                    'translation' => $editions->firstWhere('edition.type', 'translation')['ayahs'] ?? [],

                    // Audio
                    'audio' => $editions->firstWhere('edition.format', 'audio')['ayahs'] ?? [],
                ]
            ];

        } catch (Exception $e) {
            Log::error('AlQuranService::getSurahByNumber - ' . $e->getMessage());
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

                    $surahsInJuz = [];

                    foreach ($data['ayahs'] as $ayah) {
                        $surah = $ayah['surah'];
                        $surahNumber = $surah['number'];

                        if (!isset($surahsInJuz[$surahNumber])) {
                            $surahsInJuz[$surahNumber] = [
                                'number' => $surahNumber,
                                'name' => $surah['name'],
                                'english_name' => $surah['englishName'],
                            ];
                        }
                    }

                    $juzs[] = [
                        'juz' => $i,
                        'ayahs_count' => count($data['ayahs']),
                        'start_surah' => $data['ayahs'][0]['surah']['englishName'],
                        'start_ayah' => $data['ayahs'][0]['numberInSurah'],
                        'surahs' => array_values($surahsInJuz),
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
                $apiUrl = 'https://api.alquran.cloud/v1/juz/' . $number;
                $response = Http::get($apiUrl);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new Exception('Failed to fetch juz from external API.');
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
            $edition = 'bn.bengali';
            // $edition = $this->getEditionByLanguage($this->user->language_code);

            return Cache::remember(
                "surahs_{$edition}",
                now()->addMonth(),
                function () use ($edition) {

                    $response = Http::get(
                        'https://api.alquran.cloud/v1/surah',
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

    private function getUserLanguageEdition()
    {
        // get all editions for the user language
        $editionList = Edition::where('language', $this->user->language_code)
            ->where('format', 'text')
            ->get()
            ->pluck('identifier')
            ->toArray();
        // if no editions for the user language, use default language
        if (empty($editionList)) {
            $editionList = Edition::where('language', 'en')
                ->where('format', 'text')->get()
                ->pluck('identifier')
                ->toArray();
        }
        // get display settings for the user
        $displaySettings = AppDisplaySetting::where('user_id', $this->user->id)->first();
        if (empty($displaySettings)) {
            return $editionList[0];
        }
        // check if translation by is in edition list
        return $selectedEdition = in_array($displaySettings->translation_by, $editionList)
            ? $displaySettings->translation_by
            : $editionList[0];
    }

    private function getUserRecitation()
    {
        $recitation = AppDisplaySetting::where('user_id', $this->user->id)->first();
        if ($recitation) {
            return $recitation->qari ?? 'ar.abdulbasitmurattal';
        }

        return 'ar.abdulbasitmurattal';
    }

    public function showTajweedSurah(int $surahNumber = 1)
    {
        try {
            $baseUrl = 'https://api.alquran.cloud/v1/surah/' . $surahNumber;

            // 1. Tajweed (Color codes সহ)
            $tajweedResponse = Http::get($baseUrl . '/quran-tajweed');

            // 2. Uthmani Text + Bengali Translation (এক কলেই)
            $textAndTransResponse = Http::get($baseUrl . '/editions/quran-uthmani,bn.bengali');

            // 3. Audio (AlAfasy reciter - তুমি চাইলে অন্য reciter নিতে পারো)
            $audioResponse = Http::get($baseUrl . '/ar.alafasy');

            if (!$tajweedResponse->successful() || !$textAndTransResponse->successful()) {
                return back()->with('error', 'API Error');
            }

            $tajweedData = $tajweedResponse->json()['data']['ayahs'];
            $combinedData = $textAndTransResponse->json()['data'];
            $audioData = $audioResponse->json()['data']['ayahs'] ?? [];

            // Data combine করি (একই ayah number অনুসারে)
            $verses = [];
            foreach ($tajweedData as $index => $tajweedAyah) {
                $uthmaniAyah = $combinedData[0]['ayahs'][$index] ?? null;     // quran-uthmani
                $bengaliAyah = $combinedData[1]['ayahs'][$index] ?? null;    // bn.bengali
                $audioAyah = $audioData[$index] ?? null;

                $verses[] = [
                    'number' => $tajweedAyah['numberInSurah'],
                    'tajweed_text' => $tajweedAyah['text'],           // এটাতে [n [q ইত্যাদি code আছে
                    'uthmani_text' => $uthmaniAyah['text'] ?? '',
                    'bengali_text' => $bengaliAyah['text'] ?? '',
                    'audio' => $audioAyah['audio'] ?? '',
                ];
            }

            return view('quran.surah', compact('verses', 'surahNumber'));
        } catch (Exception $e) {
            Log::error('AlQuranService::showTajweedSurah ' . $e->getMessage());
            throw $e;
        }
    }
}
