<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favourite extends Model
{
    protected $fillable = [
        'user_id',
        'favouritable_id',
        'favouritable_type',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'favouritable_id' => 'integer',
            'favouritable_type' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this favourite
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent favouritable model (Category or DuaDhikr)
     */
    public function favouritable(): MorphTo
    {
        return $this->morphTo();
    }
}
