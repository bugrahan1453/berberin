<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopHour extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shop_id', 'day_of_week', 'open_time', 'close_time',
        'break_start', 'break_end', 'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'day_of_week' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
