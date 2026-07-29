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
            if (env('QURAN_DATA_SOURCE', 'api') === 'database') {
                Log::info('AlQuranService::getAllSurahs from db');
                return Cache::remember('db_all_surahs', now()->addMonth(), function () {
                    $surahs = \App\Models\Surah::orderBy('number')->get()->map(function ($s) {
                        return [
                            'number' => $s->number,
                            'name' => $s->name,
                            'englishName' => $s->english_name,
                            'englishNameTranslation' => $s->english_name_translation,
                            'numberOfAyahs' => $s->number_of_ayahs,
                            'revelationType' => $s->revelation_type,
                        ];
                    })->toArray();

                    return [
                        'code' => 200,
                        'status' => 'OK',
                        'data' => $surahs
                    ];
                });
            }

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

    public function getSurahByNumber(string $number, string $defaultEdition = 'quran-uthmani', ?string $translationEdition = null, ?string $audioEdition = null): array
    {
        try {
            $userLanguageEdition = $translationEdition ? [$translationEdition] : (array) $this->getUserLanguageEdition();
            $userRecitation = $audioEdition ? [$audioEdition] : (array) $this->getUserRecitation();

            $allEditions = array_map(function ($e) {
                return trim($e, "'\" ");
            }, array_unique(array_merge(
                    [$defaultEdition],
                    $userLanguageEdition,
                    $userRecitation,
                    ['quran-tajweed']
                )));

            if (config('services.alquran.source') === 'database') {
                $editionParam = implode(",", $allEditions);
                return Cache::remember('db_surah_' . $number . '_' . md5($editionParam), now()->addMonth(), function () use ($number, $allEditions) {
                    $surah = \App\Models\Surah::where('number', $number)->first();
                    if (!$surah)
                        throw new Exception('Surah not found in DB');

                    $editionIds = Edition::whereIn('identifier', $allEditions)->pluck('id', 'identifier');
                    $ayahs = \App\Models\Ayah::where('surah_id', $number)->orderBy('number_in_surah')->get();

                    $ayahEditions = \App\Models\AyahEdition::whereIn('ayah_id', $ayahs->pluck('number'))
                        ->whereIn('edition_id', $editionIds->values())
                        ->get()
                        ->groupBy('ayah_id');

                    $arabic = [];
                    $tajweed = [];
                    $translation = [];
                    $audio = [];

                    $uthmaniId = $editionIds['quran-uthmani'] ?? null;
                    $tajweedId = $editionIds['quran-tajweed'] ?? null;
                    // Find translation edition id
                    $transId = null;
                    foreach ($allEditions as $e) {
                        if ($e !== 'quran-uthmani' && $e !== 'quran-tajweed' && !str_starts_with($e, 'ar.')) {
                            $transId = $editionIds[$e] ?? null;
                            break;
                        }
                    }
                    // Find audio edition id
                    $audioId = null;
                    foreach ($allEditions as $e) {
                        if (str_starts_with($e, 'ar.') && $e !== 'quran-uthmani' && $e !== 'quran-tajweed') {
                            $audioId = $editionIds[$e] ?? null;
                            break;
                        }
                    }

                    foreach ($ayahs as $ayah) {
                        $editionsForAyah = $ayahEditions->get($ayah->number, collect());

                        // Uthmani
                        $uthmaniRecord = $editionsForAyah->firstWhere('edition_id', $uthmaniId);
                        if ($uthmaniRecord) {
                            $arabic[] = [
                                'number' => $ayah->number,
                                'text' => $uthmaniRecord->text,
                                'numberInSurah' => $ayah->number_in_surah,
                                'juz' => $ayah->juz,
                                'page' => $ayah->page,
                                'ruku' => $ayah->ruku,
                                'hizbQuarter' => $ayah->hizb_quarter,
                                'sajda' => $ayah->sajda,
                            ];
                        }

                        // Tajweed
                        $tajweedRecord = $editionsForAyah->firstWhere('edition_id', $tajweedId);
                        if ($tajweedRecord) {
                            $tajweed[] = ['number' => $ayah->number, 'text' => $tajweedRecord->text, 'numberInSurah' => $ayah->number_in_surah];
                        }

                        // Translation
                        $transRecord = $editionsForAyah->firstWhere('edition_id', $transId);
                        if ($transRecord) {
                            $translation[] = ['number' => $ayah->number, 'text' => $transRecord->text, 'numberInSurah' => $ayah->number_in_surah];
                        }

                        // Audio
                        $audioRecord = $editionsForAyah->firstWhere('edition_id', $audioId);
                        if ($audioRecord) {
                            $audio[] = ['number' => $ayah->number, 'audio' => $audioRecord->audio_url, 'numberInSurah' => $ayah->number_in_surah];
                        }
                    }

                    return [
                        'status' => true,
                        'message' => 'Surah fetched successfully from DB',
                        'code' => 200,
                        'data' => [
                            'meta' => [
                                'number' => $surah->number,
                                'name' => $surah->name,
                                'englishName' => $surah->english_name,
                                'englishNameTranslation' => $surah->english_name_translation,
                                'revelationType' => $surah->revelation_type,
                                'numberOfAyahs' => $surah->number_of_ayahs,
                            ],
                            'arabic' => $arabic,
                            'tajweed' => $tajweed,
                            'translation' => $translation,
                            'audio' => $audio,
                        ]
                    ];
                });
            }

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
            if (config('services.alquran.source') === 'database') {
                return Cache::remember('db_all_juzs', now()->addMonth(), function () {
                    $juzs = [];
                    for ($i = 1; $i <= 30; $i++) {
                        $ayahs = \App\Models\Ayah::with('surah')->where('juz', $i)->orderBy('number')->get();
                        if ($ayahs->isEmpty())
                            continue;

                        $surahsInJuz = [];
                        foreach ($ayahs as $ayah) {
                            $surah = $ayah->surah;
                            if (!isset($surahsInJuz[$surah->number])) {
                                $surahsInJuz[$surah->number] = [
                                    'number' => $surah->number,
                                    'name' => $surah->name,
                                    'english_name' => $surah->english_name,
                                ];
                            }
                        }

                        $firstSurah = $ayahs->first()->surah;
                        $juzs[] = [
                            'juz' => $i,
                            'ayahs_count' => $ayahs->count(),
                            'start_surah' => [
                                'name' => $firstSurah->name,
                                'english_name' => $firstSurah->english_name,
                            ],
                            'start_ayah' => $ayahs->first()->number_in_surah,
                            'surahs' => array_values($surahsInJuz),
                        ];
                    }
                    return $juzs;
                });
            }

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

                    $firstSurah = $data['ayahs'][0]['surah'];
                    $juzs[] = [
                        'juz' => $i,
                        'ayahs_count' => count($data['ayahs']),
                        'start_surah' => [
                            'name' => $firstSurah['name'],
                            'english_name' => $firstSurah['englishName'],
                        ],
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
            if (config('services.alquran.source') === 'database') {
                return Cache::remember('db_juz_' . $number, now()->addMonth(), function () use ($number) {
                    $ayahs = \App\Models\Ayah::where('juz', $number)->orderBy('number')->get();
                    if ($ayahs->isEmpty())
                        throw new Exception('Juz not found in DB');

                    $editionId = Edition::where('identifier', 'quran-uthmani')->value('id');
                    $ayahEditions = \App\Models\AyahEdition::whereIn('ayah_id', $ayahs->pluck('number'))
                        ->where('edition_id', $editionId)
                        ->get()
                        ->keyBy('ayah_id');

                    $surahIds = $ayahs->pluck('surah_id')->unique();
                    $surahs = \App\Models\Surah::whereIn('number', $surahIds)->get()->keyBy('number');

                    $ayahsData = [];
                    foreach ($ayahs as $ayah) {
                        $surah = $surahs->get($ayah->surah_id);
                        $ayahText = $ayahEditions->get($ayah->number)?->text ?? '';
                        $ayahsData[] = [
                            'number' => $ayah->number,
                            'text' => $ayahText,
                            'numberInSurah' => $ayah->number_in_surah,
                            'juz' => $ayah->juz,
                            'page' => $ayah->page,
                            'ruku' => $ayah->ruku,
                            'hizbQuarter' => $ayah->hizb_quarter,
                            'sajda' => $ayah->sajda,
                            'surah' => [
                                'number' => $surah->number,
                                'name' => $surah->name,
                                'englishName' => $surah->english_name,
                                'englishNameTranslation' => $surah->english_name_translation,
                                'revelationType' => $surah->revelation_type,
                                'numberOfAyahs' => $surah->number_of_ayahs,
                            ]
                        ];
                    }

                    return [
                        'code' => 200,
                        'status' => 'OK',
                        'data' => [
                            'number' => (int) $number,
                            'ayahs' => $ayahsData,
                            'edition' => [
                                'identifier' => 'quran-uthmani',
                                'language' => 'ar',
                                'name' => 'القرآن الكريم المكتوب (إملائي)',
                                'englishName' => 'Quran (Uthmani)',
                                'format' => 'text',
                                'type' => 'quran',
                                'direction' => 'rtl',
                            ]
                        ]
                    ];
                });
            }

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
            if (env('QURAN_DATA_SOURCE', 'api') === 'database') {
                return Cache::remember('db_all_surahs_user_lang', now()->addMonth(), function () {
                    $surahs = \App\Models\Surah::orderBy('number')->get()->map(function ($s) {
                        return [
                            'number' => $s->number,
                            'name' => $s->name,
                            'englishName' => $s->english_name,
                            'englishNameTranslation' => $s->english_name_translation,
                            'numberOfAyahs' => $s->number_of_ayahs,
                            'revelationType' => $s->revelation_type,
                        ];
                    })->toArray();

                    // Note: If you want to return translated surah names, we can adapt this later.
                    return $surahs;
                });
            }

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
            if (config('services.alquran.source') === 'database') {
                $ayahs = \App\Models\Ayah::where('surah_id', $surahNumber)->orderBy('number_in_surah')->get();
                $editionIds = Edition::whereIn('identifier', ['quran-tajweed', 'quran-uthmani', 'bn.bengali', 'ar.alafasy'])->pluck('id', 'identifier');

                $ayahEditions = \App\Models\AyahEdition::whereIn('ayah_id', $ayahs->pluck('number'))
                    ->whereIn('edition_id', $editionIds->values())
                    ->get()
                    ->groupBy('ayah_id');

                $verses = [];
                $tajweedId = $editionIds['quran-tajweed'] ?? null;
                $uthmaniId = $editionIds['quran-uthmani'] ?? null;
                $bengaliId = $editionIds['bn.bengali'] ?? null;
                $audioId = $editionIds['ar.alafasy'] ?? null;

                foreach ($ayahs as $ayah) {
                    $editionsForAyah = $ayahEditions->get($ayah->number, collect());

                    $tajweedText = $editionsForAyah->firstWhere('edition_id', $tajweedId)?->text ?? '';
                    $uthmaniText = $editionsForAyah->firstWhere('edition_id', $uthmaniId)?->text ?? '';
                    $bengaliText = $editionsForAyah->firstWhere('edition_id', $bengaliId)?->text ?? '';
                    $audioUrl = $editionsForAyah->firstWhere('edition_id', $audioId)?->audio_url ?? '';

                    $verses[] = [
                        'number' => $ayah->number_in_surah,
                        'tajweed_text' => $tajweedText,
                        'uthmani_text' => $uthmaniText,
                        'bengali_text' => $bengaliText,
                        'audio' => $audioUrl,
                    ];
                }

                return view('quran.surah', compact('verses', 'surahNumber'));
            }

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

    public function search(string $query, string $language = 'bn', int $offset = 0, int $limit = 50)
    {
        try {
            if (config('services.alquran.source') === 'database') {
                $editions = Edition::where('language', $language)
                    ->orWhere('language', 'ar') // include arabic as fallback
                    ->pluck('id');

                $results = \App\Models\AyahEdition::with(['ayah.surah'])
                    ->whereIn('edition_id', $editions)
                    ->whereFullText('text', $query)
                    ->offset($offset)
                    ->limit($limit)
                    ->get();

                $formattedResults = $results->map(function ($item) {
                    return [
                        'text' => $item->text,
                        'ayah_number' => $item->ayah_id,
                        'number_in_surah' => $item->ayah->number_in_surah,
                        'surah_name' => $item->ayah->surah->name,
                        'surah_english_name' => $item->ayah->surah->english_name,
                        'juz' => $item->ayah->juz,
                        'page' => $item->ayah->page,
                        'edition_id' => $item->edition->identifier ?? null,
                    ];
                });

                return [
                    'code' => 200,
                    'status' => 'OK',
                    'data' => [
                        'query' => $query,
                        'count' => $formattedResults->count(),
                        'offset' => $offset,
                        'limit' => $limit,
                        'matches' => $formattedResults,
                    ]
                ];
            }

            // Using AlQuran Cloud API search
            $edition = $this->getEditionByLanguage($language);
            $apiUrl = "https://api.alquran.cloud/v1/search/$query/all/$edition";
            $response = Http::get($apiUrl);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new Exception('Failed to search from external API.');
            }

        } catch (Exception $e) {
            Log::error('AlQuranService::search ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPage(int $pageNumber, string $defaultEdition = 'quran-uthmani')
    {
        try {
            if (config('services.alquran.source') === 'database') {
                $userLanguageEdition = (array) $this->getUserLanguageEdition();
                $userRecitation = (array) $this->getUserRecitation();

                $allEditions = array_map(function ($e) {
                    return trim($e, "'\" ");
                }, array_unique(array_merge(
                        [$defaultEdition],
                        $userLanguageEdition,
                        $userRecitation,
                        ['quran-tajweed']
                    )));

                $editionParam = implode(",", $allEditions);

                return Cache::remember('db_page_' . $pageNumber . '_' . md5($editionParam), now()->addMonth(), function () use ($pageNumber, $allEditions) {
                    $ayahs = \App\Models\Ayah::with('surah')->where('page', $pageNumber)->orderBy('number')->get();
                    
                    if ($ayahs->isEmpty())
                        throw new Exception('Page not found in DB');

                    $editionIds = Edition::whereIn('identifier', $allEditions)->pluck('id', 'identifier');
                    
                    $ayahEditions = \App\Models\AyahEdition::whereIn('ayah_id', $ayahs->pluck('number'))
                        ->whereIn('edition_id', $editionIds->values())
                        ->get()
                        ->groupBy('ayah_id');

                    $uthmaniId = $editionIds['quran-uthmani'] ?? null;
                    $tajweedId = $editionIds['quran-tajweed'] ?? null;
                    $transId = null;
                    foreach ($allEditions as $e) {
                        if ($e !== 'quran-uthmani' && $e !== 'quran-tajweed' && !str_starts_with($e, 'ar.')) {
                            $transId = $editionIds[$e] ?? null;
                            break;
                        }
                    }
                    $audioId = null;
                    foreach ($allEditions as $e) {
                        if (str_starts_with($e, 'ar.') && $e !== 'quran-uthmani' && $e !== 'quran-tajweed') {
                            $audioId = $editionIds[$e] ?? null;
                            break;
                        }
                    }

                    $ayahsData = [];
                    foreach ($ayahs as $ayah) {
                        $editionsForAyah = $ayahEditions->get($ayah->number, collect());
                        $surah = $ayah->surah;

                        $ayahsData[] = [
                            'number' => $ayah->number,
                            'numberInSurah' => $ayah->number_in_surah,
                            'juz' => $ayah->juz,
                            'page' => $ayah->page,
                            'ruku' => $ayah->ruku,
                            'hizbQuarter' => $ayah->hizb_quarter,
                            'sajda' => $ayah->sajda,
                            'surah' => [
                                'number' => $surah->number,
                                'name' => $surah->name,
                                'englishName' => $surah->english_name,
                                'englishNameTranslation' => $surah->english_name_translation,
                            ],
                            'arabic' => $editionsForAyah->firstWhere('edition_id', $uthmaniId)?->text ?? '',
                            'tajweed' => $editionsForAyah->firstWhere('edition_id', $tajweedId)?->text ?? '',
                            'translation' => $editionsForAyah->firstWhere('edition_id', $transId)?->text ?? '',
                            'audio' => $editionsForAyah->firstWhere('edition_id', $audioId)?->audio_url ?? '',
                        ];
                    }

                    return [
                        'code' => 200,
                        'status' => 'OK',
                        'data' => [
                            'number' => $pageNumber,
                            'ayahs' => $ayahsData,
                        ]
                    ];
                });
            }

            return Cache::remember('page_' . $pageNumber, now()->addMonth(), function () use ($pageNumber) {
                $apiUrl = 'https://api.alquran.cloud/v1/page/' . $pageNumber . '/quran-uthmani';
                $response = Http::get($apiUrl);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    throw new Exception('Failed to fetch page from external API.');
                }
            });
        } catch (Exception $e) {
            Log::error('AlQuranService::getPage ' . $e->getMessage());
            throw $e;
        }
    }
}
