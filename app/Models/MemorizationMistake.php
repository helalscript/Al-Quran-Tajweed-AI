<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorizationMistake extends Model
{
    protected $table = 'memorization_mistakes';

    protected $fillable = [
        'memorization_session_id',
        'mistake_type',
        'word_position',
        'sentence_position',
        'original_word',
        'user_word',
        'confidence_score',
        'suggestion',
        'is_corrected',
        'corrected_at',
    ];

    protected $casts = [
        'memorization_session_id' => 'integer',
        'mistake_type' => 'string',
        'word_position' => 'integer',
        'sentence_position' => 'integer',
        'confidence_score' => 'decimal:2',
        'is_corrected' => 'boolean',
        'corrected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the memorization session that owns the mistake.
     */
    public function memorizationSession(): BelongsTo
    {
        return $this->belongsTo(MemorizationSession::class);
    }
}
