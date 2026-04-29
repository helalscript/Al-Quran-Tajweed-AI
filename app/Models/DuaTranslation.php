<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuaTranslation extends Model
{
    protected $fillable = [
        'dua_dhikir_id',
        'language_code',
        'title',
        'translation',
        'notes',
        'benefits',
        'fawaid',
    ];

    protected function casts(): array
    {
        return [
            'dua_dhikir_id' => 'integer',
            'language_code' => 'string',
            'title' => 'string',
            'translation' => 'string',
            'notes' => 'string',
            'benefits' => 'string',
            'fawaid' => 'string',
        ];
    }

    /**
     * Get the dua dhikr that owns this translation
     */
    public function duaDhikir(): BelongsTo
    {
        return $this->belongsTo(DuaDhikir::class);
    }
}
