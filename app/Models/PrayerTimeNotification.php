<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerTimeNotification extends Model
{
    protected $table = 'prayer_time_notifications';

    protected $fillable = [
        'user_id',
        'fajr',
        'dhuhr',
        'asr',
        'maghrib',
        'isha',
        'sunrise',
        'sunset',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'fajr' => 'boolean',
        'dhuhr' => 'boolean',
        'asr' => 'boolean',
        'maghrib' => 'boolean',
        'isha' => 'boolean',
        'sunrise' => 'boolean',
        'sunset' => 'boolean',
        'status' => 'string',
    ];
}
