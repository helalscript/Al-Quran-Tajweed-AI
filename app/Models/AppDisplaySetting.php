<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppDisplaySetting extends Model
{
    protected $table = 'app_display_settings';

    protected $fillable = [
        'user_id',
        'font_size',
        'appearance',
        'tajweed_color_guide',
        'arabic_script',
        'typography_background_color',
        'translation_by',
        // Advanced Settings
        'delay_between_verse',
        'playback_speed',
        'stop_after_range',
        'stream_without_downloading',
        'scroll_while_playing',
        'word_by_word_audio_highlighting',
        // Audio Settings
        'qari',
        'repeat_verse',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'font_size' => 'decimal:1',
        'appearance' => 'string',
        'tajweed_color_guide' => 'boolean',
        'arabic_script' => 'string',
        'typography_background_color' => 'string',
        'translation_by' => 'string',
        // Advanced Settings
        'delay_between_verse' => 'string',
        'playback_speed' => 'string',
        'stop_after_range' => 'boolean',
        'stream_without_downloading' => 'boolean',
        'scroll_while_playing' => 'boolean',
        'word_by_word_audio_highlighting' => 'boolean',
        // Audio Settings
        'qari' => 'string',
        'repeat_verse' => 'string',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
