<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuaDhikir extends Model
{
    /** @use HasFactory<\Database\Factories\DuaDhikirFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'arabic_text',
        'translation',
        'description',
        'image',
        'status',
    ];
}
