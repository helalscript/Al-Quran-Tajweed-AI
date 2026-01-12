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

    /**
     * Get all dua dhikrs for this category
     */
    public function duaDhikrs(): HasMany
    {
        return $this->hasMany(DuaDhikr::class);
    }

    /**
     * Get all favourites for this category
     */
    public function favourites(): MorphMany
    {
        return $this->morphMany(Favourite::class, 'favouritable');
    }
}
