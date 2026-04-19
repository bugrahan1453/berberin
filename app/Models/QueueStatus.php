<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueStatus extends Model
{
    public $timestamps = false;

    const UPDATED_AT = 'updated_at';
    const CREATED_AT = null;

    protected $fillable = [
        'shop_id', 'current_waiting', 'avg_wait_minutes', 'is_full',
    ];

    protected $casts = [
        'is_full' => 'boolean',
        'updated_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
