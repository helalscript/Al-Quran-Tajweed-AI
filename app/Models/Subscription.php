<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rc_original_app_user_id',
        'product_id',
        'entitlement_id',
        'status',
        'expires_at',
        'purchased_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'purchased_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
