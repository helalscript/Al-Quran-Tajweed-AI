<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizationSession extends Model
{
    protected $table = 'memorization_sessions';

    protected $fillable = [
        'user_id',
        'surah_id',
        'surah_name',
        'ayah_id',
        'ayah_text',
        'ayah_start',
        'ayah_end',
        'original_text',
        'user_recitation',
        'status',
        'accuracy_score',
        'total_mistakes',
        'ai_response',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'surah_id' => 'integer',
        'ayah_id' => 'integer',
        'ayah_start' => 'integer',
        'ayah_end' => 'integer',
        'status' => 'string',
        'accuracy_score' => 'decimal:2',
        'total_mistakes' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the memorization session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the mistakes for the memorization session.
     */
    public function mistakes(): HasMany
    {
        return $this->hasMany(MemorizationMistake::class);
    }
}
