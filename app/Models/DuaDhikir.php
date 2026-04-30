<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DuaDhikir extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'arabic',
        'source',
        'image',
        'audio_url',
        'order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'arabic' => 'string',
            'source' => 'string',
            'image'=> 'string',
            'audio_url' => 'string',
            'order' => 'integer',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the translations for this dua dhikr
     */
    public function translations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DuaTranslation::class);
    }

    /**
     * Get the category that owns this dua dhikr
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all favourites for this dua dhikr
     */
    public function favourites(): MorphMany
    {
        return $this->morphMany(Favourite::class, 'favouritable');
    }

    public function getImageAttribute($value): string|null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url('storage/' . $value);
        }

        // Return only the path for web requests
        return $value;
    }

}
