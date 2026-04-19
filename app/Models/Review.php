<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id', 'shop_id', 'appointment_id',
        'rating', 'comment', 'reply', 'replied_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'replied_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
