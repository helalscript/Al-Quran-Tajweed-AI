<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\PrayerTimeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrayerTimeController extends Controller
{
    public function __construct(protected PrayerTimeService $prayerTimeService)
    {
        //
    }
    public function getPrayerTimes(Request $request)
    {
        $validatedData = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'method' => 'sometimes|integer',
        ]);
        try {
            return $this->prayerTimeService->getPrayerTimes($validatedData);
        } catch (Exception $e) {
            Log::error("PrayerTimeController::getPrayerTimes" . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch prayer times'], 500);
        }
    }

    public function getPrayerTimeNotificationSettings()
    {
        try {
            $prayerTimeNotificationSettings = $this->prayerTimeService->getPrayerTimeNotificationSettings();
            return Helper::jsonResponse(true, 'Prayer time notification settings fetched successfully', 200, $prayerTimeNotificationSettings);
        } catch (Exception $e) {
            Log::error("PrayerTimeController::getPrayerTimeNotificationSettings" . $e->getMessage());
            return Helper::jsonErrorResponse('Unable to fetch prayer time notification settings', 500);
        }
    }

    public function updatePrayerTimeNotificationSettings(Request $request)
    {
        $validatedData = $request->validate([
            'fajr' => 'required|boolean',
            'dhuhr' => 'required|boolean',
            'asr' => 'required|boolean',
            'maghrib' => 'required|boolean',
            'isha' => 'required|boolean',
            'sunrise' => 'required|boolean',
            'sunset' => 'required|boolean',
        ]);
        try {
            $this->prayerTimeService->updatePrayerTimeNotificationSettings($validatedData);
            return Helper::jsonResponse(true, 'Prayer time notification settings updated successfully', 200);
        } catch (Exception $e) {
            Log::error("PrayerTimeController::updatePrayerTimeNotificationSettings" . $e->getMessage());
            return Helper::jsonErrorResponse('Unable to update prayer time notification settings', 500);
        }
    }
}
