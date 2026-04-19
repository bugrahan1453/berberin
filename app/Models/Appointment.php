<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id', 'shop_id', 'staff_id', 'service_id', 'seat_id',
        'date', 'time', 'end_time', 'status', 'price', 'source',
        'customer_name', 'customer_phone',
        'confirmed_at', 'started_at', 'completed_at', 'cancelled_at',
        'cancelled_by', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'float',
        'confirmed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function seat()
    {
        return $this->belongsTo(Seat::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
