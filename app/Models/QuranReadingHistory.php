<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranReadingHistory extends Model
{
    protected $fillable = [
        'user_id',
        'surah_number',
        'ayah_number',
        'page_number',
        'last_read_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
