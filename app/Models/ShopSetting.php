<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shop_id', 'slot_interval', 'min_advance_hours', 'max_advance_days',
        'cancel_hours', 'auto_approve', 'deposit_required', 'deposit_amount',
        'deposit_percentage', 'max_daily_per_user', 'walkin_enabled',
    ];

    protected $casts = [
        'auto_approve' => 'boolean',
        'deposit_required' => 'boolean',
        'walkin_enabled' => 'boolean',
        'deposit_amount' => 'float',
        'deposit_percentage' => 'float',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
