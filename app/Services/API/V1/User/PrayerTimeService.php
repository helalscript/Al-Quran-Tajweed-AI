<?php

namespace App\Services\API\V1\User;

use App\Models\PrayerTimeNotification;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Pool;

class PrayerTimeService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function getPrayerTimes($validatedData)
    {
        try {
            // Define the API URL with parameters
            $latitude = $validatedData['latitude'];
            $longitude = $validatedData['longitude'];
            $method = $validatedData['method'] ?? 1;  // You can change this value based on the method parameter you need

            // API endpoint
            $url = "https://api.aladhan.com/v1/timings?latitude=$latitude&longitude=$longitude&method=$method";

            // Make the API request using Laravel's HTTP client
            $response = Http::get($url);

            // Check if the response is successful
            if ($response->successful()) {
                // Parse the response body
                $data = $response->json();

                // Optionally, return the data to the view or process it further
                return response()->json($data);
            } else {
                // Handle the error if the request was unsuccessful
                throw new Exception('Unable to fetch prayer times');
            }
        } catch (Exception $e) {
            Log::error("PrayerTimeService::getPrayerTimes" . $e->getMessage());
            throw $e;
        }
    }

    public function getPrayerTimeNotificationSettings()
    {
        try {
            $prayerTimeNotificationSettings = PrayerTimeNotification::where('user_id', $this->user->id)
                ->select('fajr', 'dhuhr', 'asr', 'maghrib', 'isha', 'sunrise', 'sunset')
                ->first();
            if (!$prayerTimeNotificationSettings) {
                //create the default settings
                $prayerTimeNotificationSettings = PrayerTimeNotification::create([
                    'user_id' => $this->user->id,
                    'fajr' => true,
                    'dhuhr' => true,
                    'asr' => true,
                    'maghrib' => true,
                    'isha' => true,
                    'sunrise' => true,
                    'sunset' => true,
                ]);
            }
            return $prayerTimeNotificationSettings;
        } catch (Exception $e) {
            Log::error("PrayerTimeService::getPrayerTimeNotificationSettings" . $e->getMessage());
            throw $e;
        }
    }

    public function updatePrayerTimeNotificationSettings($validatedData)
    {
        try {
            $prayerTimeNotificationSettings = PrayerTimeNotification::updateOrCreate(
                ['user_id' => $this->user->id],
                $validatedData
            );
            return $prayerTimeNotificationSettings;
        } catch (Exception $e) {
            Log::error("PrayerTimeService::updatePrayerTimeNotificationSettings" . $e->getMessage());
            throw $e;
        }
    }


    public function getPrayerTimesWithCountries(array $validatedData)
    {
        try {
            $method = $validatedData['method'] ?? 1;

            $locations = [
                'current' => [
                    'latitude' => $validatedData['latitude'],
                    'longitude' => $validatedData['longitude'],
                ],
                'malaysia' => [
                    'latitude' => 3.1390,      // Kuala Lumpur
                    'longitude' => 101.6869,
                ],
                'indonesia' => [
                    'latitude' => -6.2088,     // Jakarta
                    'longitude' => 106.8456,
                ],
                'singapore' => [
                    'latitude' => 1.3521,
                    'longitude' => 103.8198,
                ],
            ];

            $responses = Http::pool(function (Pool $pool) use ($locations, $method) {
                return [
                    $pool->as('current')->get('https://api.aladhan.com/v1/timings', [
                        'latitude' => $locations['current']['latitude'],
                        'longitude' => $locations['current']['longitude'],
                        'method' => $method,
                    ]),

                    $pool->as('malaysia')->get('https://api.aladhan.com/v1/timings', [
                        'latitude' => $locations['malaysia']['latitude'],
                        'longitude' => $locations['malaysia']['longitude'],
                        'method' => $method,
                    ]),

                    $pool->as('indonesia')->get('https://api.aladhan.com/v1/timings', [
                        'latitude' => $locations['indonesia']['latitude'],
                        'longitude' => $locations['indonesia']['longitude'],
                        'method' => $method,
                    ]),

                    $pool->as('singapore')->get('https://api.aladhan.com/v1/timings', [
                        'latitude' => $locations['singapore']['latitude'],
                        'longitude' => $locations['singapore']['longitude'],
                        'method' => $method,
                    ]),
                ];
            });

            return response()->json([
                'current' => $responses['current']->json(),
                'malaysia' => $responses['malaysia']->json(),
                'indonesia' => $responses['indonesia']->json(),
                'singapore' => $responses['singapore']->json(),
            ]);

        } catch (\Exception $e) {
            Log::error("PrayerTimeService::getPrayerTimesWithCountries " . $e->getMessage());
            throw $e;
        }
    }

    public function getSingaporeOrLocalPrayerTimes($validatedData)
    {
        try {
            $timezone = new \DateTimeZone('Asia/Singapore');
            $dateTime = new \DateTime('now', $timezone);
            $currentDate = $dateTime->format('Y-m-d');
            $dateForHijri = $dateTime->format('d-m-Y');

            $latitude = $validatedData['latitude'] ?? null;
            $longitude = $validatedData['longitude'] ?? null;
            $method = $validatedData['method'] ?? 1;

            $cacheKey = "prayer_times_" . ($latitude ?? 'default') . "_" . ($longitude ?? 'default') . "_" . $method . "_" . $currentDate;

            $formattedData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($latitude, $longitude, $method, $currentDate, $dateForHijri, $dateTime) {
                // Fetch AlAdhan API requests in parallel using pool
                $responses = Http::pool(function (Pool $pool) use ($latitude, $longitude, $method, $dateForHijri) {
                    $calls = [
                        $pool->as('malaysia')->get('https://api.aladhan.com/v1/timings', [
                            'latitude' => 3.1390,
                            'longitude' => 101.6869,
                            'method' => 17, // JAKIM
                        ]),
                        $pool->as('indonesia')->get('https://api.aladhan.com/v1/timings', [
                            'latitude' => -6.2088,
                            'longitude' => 106.8456,
                            'method' => 20, // Kemenag
                        ]),
                        $pool->as('hijri_singapore')->get("https://api.aladhan.com/v1/gToH/{$dateForHijri}", [
                            'calendarMethod' => 'MATHEMATICAL',
                            'adjustment' => 1,
                        ]),
                    ];

                    if ($latitude !== null && $longitude !== null) {
                        $calls[] = $pool->as('local')->get('https://api.aladhan.com/v1/timings', [
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'method' => $method,
                        ]);
                    }

                    return $calls;
                });

                // Fetch Singapore times from data.gov.sg
                $resourceId = 'd_a6a206cba471fe04b62dd886ef5eaf22';
                $dataGovUrl = "https://data.gov.sg/api/action/datastore_search?resource_id=" . $resourceId . "&filters=" . urlencode(json_encode(['Date' => $currentDate]));

                $responseSG = Http::get($dataGovUrl);
                $dataGovResult = null;
                if ($responseSG->successful()) {
                    $jsonData = $responseSG->json();
                    if (!empty($jsonData['result']['records'])) {
                        $dataGovResult = $jsonData['result']['records'][0];
                    }
                }

                if (!$dataGovResult) {
                    // Fallback to AlAdhan timings for Singapore
                    $fallbackSGResponse = Http::get('https://api.aladhan.com/v1/timings', [
                        'latitude' => 1.3521,
                        'longitude' => 103.8198,
                        'method' => 11, // MUIS
                    ]);
                    if ($fallbackSGResponse->successful()) {
                        $fallbackData = $fallbackSGResponse->json();
                        $sgTimings = $fallbackData['data']['timings'];
                    } else {
                        throw new \Exception('Unable to fetch Singapore prayer times from data.gov.sg or AlAdhan fallback');
                    }
                } else {
                    $sgTimings = [
                        'Fajr' => $dataGovResult['Subuh'] ?? null,
                        'Sunrise' => $dataGovResult['Syuruk'] ?? null,
                        'Dhuhr' => $dataGovResult['Zohor'] ?? null,
                        'Asr' => $dataGovResult['Asar'] ?? null,
                        'Sunset' => $dataGovResult['Maghrib'] ?? null,
                        'Maghrib' => $dataGovResult['Maghrib'] ?? null,
                        'Isha' => $dataGovResult['Isyak'] ?? null,
                        'Imsak' => isset($dataGovResult['Subuh']) ? date('H:i', strtotime($dataGovResult['Subuh']) - 600) : null,
                        'Midnight' => null,
                        'Firstthird' => null,
                        'Lastthird' => null,
                    ];
                }

                $malaysiaData = $responses['malaysia']->successful() ? $responses['malaysia']->json()['data'] : null;
                $indonesiaData = $responses['indonesia']->successful() ? $responses['indonesia']->json()['data'] : null;
                $hijriSGData = $responses['hijri_singapore']->successful() ? $responses['hijri_singapore']->json() : null;
                $localData = isset($responses['local']) && $responses['local']->successful() ? $responses['local']->json()['data'] : null;

                $data = [
                    'singapore' => [
                        'timings' => $sgTimings,
                        'date' => [
                            'readable' => $dateTime->format('d M Y'),
                            'timestamp' => (string) $dateTime->getTimestamp(),
                            'gregorian' => $hijriSGData['data']['gregorian'] ?? [
                                'date' => $dateTime->format('d-m-Y'),
                                'format' => 'DD-MM-YYYY',
                                'day' => $dateTime->format('d'),
                                'weekday' => [
                                    'en' => $dateTime->format('l'),
                                ],
                                'month' => [
                                    'number' => (int) $dateTime->format('m'),
                                    'en' => $dateTime->format('F'),
                                ],
                                'year' => $dateTime->format('Y'),
                            ],
                            'hijri' => $hijriSGData['data']['hijri'] ?? [],
                        ]
                    ],
                    'malaysia' => [
                        'timings' => $malaysiaData['timings'] ?? null,
                        'date' => $malaysiaData['date'] ?? null,
                    ],
                    'indonesia' => [
                        'timings' => $indonesiaData['timings'] ?? null,
                        'date' => $indonesiaData['date'] ?? null,
                    ]
                ];

                if ($latitude !== null && $longitude !== null) {
                    $data['local'] = [
                        'timings' => $localData['timings'] ?? null,
                        'date' => $localData['date'] ?? null,
                        'meta' => $localData['meta'] ?? null,
                    ];
                }

                return $data;
            });

            return response()->json([
                'code' => 200,
                'status' => 'OK',
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            Log::error("PrayerTimeService::getSingaporeOrLocalPrayerTimes: " . $e->getMessage());
            throw $e;
        }
    }

}