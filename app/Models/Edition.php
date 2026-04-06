<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Edition extends Model
{
    protected $fillable = [
        'identifier',
        'language',
        'name',
        'english_name',
        'format',
        'type',
        'direction',
    ];
    protected function casts(): array
    {
        return [
            'identifier' => 'string',
            'language' => 'string',
            'name' => 'string',
            'english_name' => 'string',
            'format' => 'string',
            'type' => 'string',
            'direction' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
