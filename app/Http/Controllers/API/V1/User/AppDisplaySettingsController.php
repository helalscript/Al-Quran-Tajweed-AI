<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\AppDisplaySettingsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppDisplaySettingsController extends Controller
{
    public function __construct(protected AppDisplaySettingsService $appDisplaySettingsService)
    {
        //
    }

    /**
     * Get display settings for the authenticated user.
     */
    public function getDisplaySettings()
    {
        try {
            $displaySettings = $this->appDisplaySettingsService->getDisplaySettings();

            return Helper::jsonResponse(true, 'Display settings fetched successfully', 200, $displaySettings);
        } catch (Exception $e) {
            Log::error('AppDisplaySettingsController::getDisplaySettings'.$e->getMessage());

            return Helper::jsonErrorResponse('Unable to fetch display settings', 500);
        }
    }

    /**
     * Update display settings for the authenticated user.
     */
    public function updateDisplaySettings(Request $request)
    {
        $validatedData = $request->validate([
            'font_size' => 'sometimes|numeric|min:10|max:50',
            'appearance' => 'sometimes|in:light,dark',
            'tajweed_color_guide' => 'sometimes|boolean',
            'arabic_script' => 'sometimes|in:uthmani,mushaf_al,quran_standard_bahriyah,indopak',
            'typography_background_color' => 'sometimes|string|in:white,black,gray,brown,red,orange,yellow,green,blue,purple,pink,gray,brown,red,orange,yellow,green,blue,purple,pink',
            'translation_by' => 'sometimes|nullable|string|max:255',
            // Advanced Settings
            'delay_between_verse' => 'sometimes|string|in:none,0.5 sec,1 sec,2 sec,5 sec,10 sec,30 sec,ayat_length',
            'playback_speed' => 'sometimes|in:slower,normal,faster',
            'stop_after_range' => 'sometimes|boolean',
            'stream_without_downloading' => 'sometimes|boolean',
            'scroll_while_playing' => 'sometimes|boolean',
            'word_by_word_audio_highlighting' => 'sometimes|boolean',
            // Audio Settings
            'qari' => 'sometimes|nullable|string|max:255',
            'repeat_verse' => 'sometimes|string|in:none,1,2,3,4,5,10,infinite',
        ]);

        try {
            $displaySettings = $this->appDisplaySettingsService->updateDisplaySettings($validatedData);

            return Helper::jsonResponse(true, 'Display settings updated successfully', 200, $displaySettings);
        } catch (Exception $e) {
            Log::error('AppDisplaySettingsController::updateDisplaySettings'.$e->getMessage());

            return Helper::jsonErrorResponse('Unable to update display settings', 500);
        }
    }
}
                