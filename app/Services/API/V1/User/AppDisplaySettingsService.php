<?php

namespace App\Services\API\V1\User;

use App\Models\AppDisplaySetting;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AppDisplaySettingsService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Get display settings for the authenticated user.
     */
    public function getDisplaySettings()
    {
        try {
            $displaySettings = AppDisplaySetting::where('user_id', $this->user->id)
                ->first();

            if (!$displaySettings) {
                // Create default settings if not exists
                $displaySettings = AppDisplaySetting::create([
                    'user_id' => $this->user->id,
                    'font_size' => 16.0,
                    'appearance' => 'dark',
                    'tajweed_color_guide' => true,
                    'arabic_script' => 'uthmani',
                    'typography_background_color' => 'white',
                    'translation_by' => null,
                    'delay_between_verse' => 'none',
                    'playback_speed' => 'normal',
                    'stop_after_range' => false,
                    'stream_without_downloading' => false,
                    'scroll_while_playing' => true,
                    'word_by_word_audio_highlighting' => true,
                    'qari' => null,
                    'repeat_verse' => 'none',
                ]);
            }

            return $displaySettings;
        } catch (Exception $e) {
            Log::error("AppDisplaySettingsService::getDisplaySettings" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update display settings for the authenticated user.
     */
    public function updateDisplaySettings($validatedData)
    {
        try {
            $displaySettings = AppDisplaySetting::updateOrCreate(
                ['user_id' => $this->user->id],
                $validatedData
            );
            return $displaySettings;
        } catch (Exception $e) {
            Log::error("AppDisplaySettingsService::updateDisplaySettings" . $e->getMessage());
            throw $e;
        }
    }
}

