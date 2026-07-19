<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ayah extends Model
{
    protected $guarded = [];

    public function surah()
    {
        return $this->belongsTo(Surah::class, 'surah_id', 'number');
    }

    public function editions()
    {
        return $this->hasMany(AyahEdition::class, 'ayah_id', 'number');
    }
}
