<?php

namespace App\Services\API\V1\User;

use App\Models\PrayerTimeNotification;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

}