<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'shop_id', 'name', 'price', 'duration_min',
        'category', 'gender', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'duration_min' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
