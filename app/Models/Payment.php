<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'appointment_id', 'amount', 'deposit_amount',
        'method', 'status', 'iyzico_ref', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'deposit_amount' => 'float',
        'paid_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
