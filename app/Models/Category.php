<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'translations',
        'image',
        'order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'translations' => 'array',
            'order' => 'integer',
            'status' => 'string',
            'type' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function getImageAttribute($value): string|null
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

    /**
     * Get all dua dhikrs for this category
     */
    public function duaDhikirs(): HasMany
    {
        return $this->hasMany(DuaDhikir::class);
    }

    /**
     * Get all favourites for this category
     */
    public function favourites(): MorphMany
    {
        return $this->morphMany(Favourite::class, 'favouritable');
    }
}
