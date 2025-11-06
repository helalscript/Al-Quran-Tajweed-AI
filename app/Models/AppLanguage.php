<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppLanguage extends Model
{
    protected $table = 'app_languages';

    protected $fillable = [
        'name',
        'code',
        'flag_icon',
        'is_default',
        'status',
    ];

    protected $casts = [
        'name' => 'string',
        'code' => 'string',
        'flag_icon' => 'string',
        'is_default' => 'boolean',
        'status' => 'boolean',
    ];

     public function getFlagIconAttribute($value): string|null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }
}
