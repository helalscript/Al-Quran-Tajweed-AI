<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AyahEdition extends Model
{
    protected $guarded = [];

    public function ayah()
    {
        return $this->belongsTo(Ayah::class, 'ayah_id', 'number');
    }

    public function edition()
    {
        return $this->belongsTo(Edition::class, 'edition_id', 'id');
    }
}
