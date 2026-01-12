<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DuaDhikr extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'arabic',
        'latin',
        'translation',
        'notes',
        'benefits',
        'fawaid',
        'source',
        'language_code',
        'audio_url',
        'order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'title' => 'string',
            'arabic' => 'string',
            'latin' => 'string',
            'translation' => 'string',
            'notes' => 'string',
            'benefits' => 'string',
            'fawaid' => 'string',
            'source' => 'string',
            'language_code' => 'string',
            'audio_url' => 'string',
            'order' => 'integer',
            'status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
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
}
